import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Breadcrumb, type BreadcrumbItem } from "@/components/atoms/Breadcrumb"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { StatusBadge, type StatusTone } from "@/components/atoms/StatusBadge"
import {
  advertisesAPI,
  type Advertise,
  type AdvertiseStatusFilter,
} from "@/api/advertises/advertisesAPIHandler"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"

// ── Column metadata for `/datatable-advertises` ─────────────────────────────
// Index order mirrors what the backend expects. `title` is searchable and
// orderable; `company.company_name` is searchable-only; `status` and
// `change_action` are display-only.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "title",                searchable: true,  orderable: true  },
  { name: "company.company_name", searchable: true,  orderable: false },
  { name: "status",               searchable: false, orderable: false },
  { name: "change_action",        searchable: false, orderable: false },
]

// ── Shared list page ─────────────────────────────────────────────────────────
// Powers both the Approved and Pending tabs of "Advertises". The two pages
// differ only by breadcrumb, status filter (1 vs 0), and the status badge
// label/tone.

interface AdvertisesListPageProps {
  breadcrumb: BreadcrumbItem[]
  statusFilter: AdvertiseStatusFilter
  statusLabel: string
  statusTone: StatusTone
}

export function AdvertisesListPage({
  breadcrumb,
  statusFilter,
  statusLabel,
  statusTone,
}: AdvertisesListPageProps) {
  const [data, setData] = useState<Advertise[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState("")
  // No default sort: first fetch sends order: [] (matches the spec).
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  // Increments per request; sent back as-is by the backend so we can detect
  // stale responses and ignore them.
  const drawRef = useRef(0)

  const fetchAdvertises = useCallback(() => {
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
      order:
        sortIndex >= 0 && sortDir
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

    advertisesAPI
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
    fetchAdvertises()
  }, [fetchAdvertises])

  const columns = useMemo<DataTableColumn<Advertise>[]>(
    () => [
      {
        key: "title",
        header: "Title",
        sortable: true,
        cell: (row) => row.title || "—",
        cellClassName: "font-medium text-gray-700",
      },
      {
        key: "company.company_name",
        header: "Company Name",
        cell: (row) => row.company?.company_name ?? "—",
      },
      {
        key: "status",
        header: "Status",
        width: "160px",
        cell: () => <StatusBadge label={statusLabel} tone={statusTone} />,
      },
      {
        key: "change_action",
        header: "Action",
        width: "120px",
        cell: () => <span className="text-gray-400">—</span>,
      },
    ],
    [statusLabel, statusTone]
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

      <DataTable<Advertise>
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
    </div>
  )
}
