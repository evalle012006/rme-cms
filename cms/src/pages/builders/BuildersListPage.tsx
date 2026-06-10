import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Link } from "react-router-dom"
import { Button } from "@/components/atoms/Button"
import { Breadcrumb, type BreadcrumbItem } from "@/components/atoms/Breadcrumb"
import { ConfirmDialog } from "@/components/atoms/ConfirmDialog"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { StatusBadge, type StatusTone } from "@/components/atoms/StatusBadge"
import {
  buildersAPI,
  BUILDER_STATUS,
  type Builder,
  type BuilderStatusFilter,
} from "@/api/builders/buildersAPIHandler"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"
import { ROUTES } from "@/constants/routes"

// ── Column metadata for `/datatable-builders` ────────────────────────────────
// Index order mirrors what the backend expects. `firstname` and `email` are
// the only orderable columns; `company.company_name` is searchable-only.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "firstname",            searchable: true,  orderable: true  },
  { name: "email",                searchable: true,  orderable: true  },
  { name: "company.company_name", searchable: true,  orderable: false },
  { name: "change_action",        searchable: false, orderable: false },
  { name: "action",               searchable: false, orderable: false },
]

// ── Shared list page ─────────────────────────────────────────────────────────
// Powers both the approved and pending tabs of "Company Users". The two pages
// differ only by breadcrumb, status filter (1 vs 0), and the status badge
// label/tone. When `flipStatusTo` is set the status badge becomes a clickable
// button that opens a confirmation dialog; confirming hits
// `/builder/change-builder-status` and refetches the list.

interface BuildersListPageProps {
  breadcrumb: BreadcrumbItem[]
  statusFilter: BuilderStatusFilter
  statusLabel: string
  statusTone: StatusTone
  // When provided, the status badge is a clickable button that flips the row
  // to this target status (after the confirmation modal is accepted).
  flipStatusTo?: BuilderStatusFilter
  // Copy for the confirmation modal that appears before the flip.
  flipConfirmMessage?: string
  // Toast message shown after a successful status change.
  flipSuccessMessage?: string
}

export function BuildersListPage({
  breadcrumb,
  statusFilter,
  statusLabel,
  statusTone,
  flipStatusTo,
  flipConfirmMessage,
  flipSuccessMessage,
}: BuildersListPageProps) {
  const [data, setData] = useState<Builder[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState("")
  // No default sort: first fetch sends order: [] (matches the spec).
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  // Id of the row whose status change is being confirmed (null = no modal).
  const [confirmId, setConfirmId] = useState<number | null>(null)

  // Increments per request; sent back as-is by the backend so we can detect
  // stale responses and ignore them.
  const drawRef = useRef(0)

  const fetchBuilders = useCallback(() => {
    setLoading(true)
    drawRef.current += 1
    const draw = drawRef.current

    const sortIndex = sortKey
      ? COLUMN_META.findIndex((c) => c.name === sortKey)
      : -1
    const start = (page - 1) * perPage

    const payload: DataTablesRequest = {
      draw,
      start,
      length: perPage,
      search: { value: search, regex: false },
      order: sortIndex >= 0 && sortDir
        ? [{ column: sortIndex, dir: sortDir }]
        : [],
      columns: COLUMN_META.map((meta, index) => ({
        data: index,
        name: meta.name,
        searchable: meta.searchable,
        orderable: meta.orderable,
        search: { value: "", regex: false },
      })),
    }

    buildersAPI
      .datatable(payload, statusFilter)
      .then((res) => {
        if (res.draw !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir, statusFilter])

  // Reset page/sort/search when the tab (status filter) changes.
  useEffect(() => {
    setPage(1)
    setSortKey(undefined)
    setSortDir(undefined)
    setSearch("")
  }, [statusFilter])

  useEffect(() => {
    fetchBuilders()
  }, [fetchBuilders])

  const confirmStatusFlip = useCallback(async () => {
    if (confirmId === null || flipStatusTo === undefined) return
    try {
      await buildersAPI.changeStatus(confirmId, flipStatusTo, {
        successMessage: flipSuccessMessage,
      })
      fetchBuilders()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [confirmId, flipStatusTo, flipSuccessMessage, fetchBuilders])

  // Column config depends on the status tab (label/tone) and whether the
  // status badge is interactive on this page.
  const columns = useMemo<DataTableColumn<Builder>[]>(
    () => [
      {
        key: "firstname",
        header: "Name",
        sortable: true,
        cell: (row) => row.full_name || row.firstname || "—",
        cellClassName: "font-medium text-gray-700",
      },
      {
        key: "email",
        header: "Email",
        sortable: true,
      },
      {
        key: "company.company_name",
        header: "Company",
        cell: (row) => row.company?.company_name ?? "—",
      },
      {
        key: "change_action",
        header: "Status",
        width: "160px",
        cell: (row) => (
          <StatusBadge
            label={statusLabel}
            tone={statusTone}
            onClick={
              flipStatusTo !== undefined ? () => setConfirmId(row.id) : undefined
            }
          />
        ),
      },
      {
        key: "action",
        header: "Action",
        width: "120px",
        cell: (row) => (
          <Link
            to={`${ROUTES.builder.edit(row.id)}?from=${
              statusFilter === BUILDER_STATUS.APPROVED ? "approved" : "pending"
            }`}
          >
            <Button color="primary" size="sm" className="h-auto py-1.5">
              Edit
            </Button>
          </Link>
        ),
      },
    ],
    [statusFilter, statusLabel, statusTone, flipStatusTo]
  )

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage)
    setPage(1)
  }

  const handleSearchChange = (newSearch: string) => {
    setSearch(newSearch)
    setPage(1)
  }

  const handleSortChange = (key: string, dir: SortDir) => {
    setSortKey(key)
    setSortDir(dir)
    setPage(1)
  }

  // Derived pagination fields from total + page + perPage.
  const lastPage = Math.max(1, Math.ceil(total / perPage))
  const from = total === 0 ? null : (page - 1) * perPage + 1
  const to = total === 0 ? null : Math.min(page * perPage, total)

  return (
    <div className="space-y-10">
      <Breadcrumb items={breadcrumb} />

      <DataTable<Builder>
        columns={columns}
        data={data}
        page={page}
        perPage={perPage}
        total={total}
        from={from}
        to={to}
        lastPage={lastPage}
        search={search}
        loading={loading}
        sortKey={sortKey}
        sortDir={sortDir}
        onPageChange={setPage}
        onPerPageChange={handlePerPageChange}
        onSearchChange={handleSearchChange}
        onSortChange={handleSortChange}
      />

      <ConfirmDialog
        open={confirmId !== null}
        onOpenChange={(next) => {
          if (!next) setConfirmId(null)
        }}
        title="Change status ?"
        description={flipConfirmMessage}
        confirmLabel="Yes"
        cancelLabel="Cancel"
        onConfirm={confirmStatusFlip}
      />
    </div>
  )
}
