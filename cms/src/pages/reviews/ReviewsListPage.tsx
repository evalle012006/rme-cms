import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Link } from "react-router-dom"
import { Button } from "@/components/atoms/Button"
import { Breadcrumb, type BreadcrumbItem } from "@/components/atoms/Breadcrumb"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { StatusBadge, type StatusTone } from "@/components/atoms/StatusBadge"
import {
  reviewsAPI,
  type Review,
  type ReviewStatusFilter,
} from "@/api/reviews/reviewsAPIHandler"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"
import { ROUTES } from "@/constants/routes"

// ── Column metadata for `/datatable-reviews` ────────────────────────────────
// Index order mirrors what the backend expects. Only `title` is searchable
// and orderable. The 4th column has a deliberately empty `name` — the legacy
// payload sends it that way, so we preserve it here to keep the request shape
// identical to the production app.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "title",          searchable: true,  orderable: true  },
  { name: "status",         searchable: false, orderable: false },
  { name: "recommendation", searchable: false, orderable: false },
  { name: "",               searchable: false, orderable: false },
  { name: "action",         searchable: false, orderable: false },
]

// ── Recommendation cell ──────────────────────────────────────────────────────
// The backend may send either a string label or a numeric 0/1 flag depending
// on the row. Normalise both to a readable label here.
function renderRecommendation(value: Review["recommendation"]) {
  if (value === null || value === undefined || value === "") return "—"
  if (typeof value === "number") {
    return value === 1 ? "Recommend" : "Don't Recommend"
  }
  return value
}

// ── Shared list page ─────────────────────────────────────────────────────────
// Powers both the Approved and Pending tabs of "Reviews". The two pages
// differ only by breadcrumb, status filter (1 vs 0), and the status badge
// label/tone.

interface ReviewsListPageProps {
  breadcrumb: BreadcrumbItem[]
  statusFilter: ReviewStatusFilter
  statusLabel: string
  statusTone: StatusTone
}

export function ReviewsListPage({
  breadcrumb,
  statusFilter,
  statusLabel,
  statusTone,
}: ReviewsListPageProps) {
  const [data, setData] = useState<Review[]>([])
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

  const fetchReviews = useCallback(() => {
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

    reviewsAPI
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
    fetchReviews()
  }, [fetchReviews])

  const columns = useMemo<DataTableColumn<Review>[]>(
    () => [
      {
        key: "title",
        header: "Title",
        sortable: true,
        cell: (row) => row.title || "—",
        cellClassName: "font-medium text-gray-700",
      },
      {
        key: "status",
        header: "Status",
        width: "160px",
        cell: () => <StatusBadge label={statusLabel} tone={statusTone} />,
      },
      {
        key: "recommendation",
        header: "Recommendation",
        cell: (row) => renderRecommendation(row.recommendation),
      },
      {
        key: "action",
        header: "Action",
        width: "120px",
        cell: (row) => (
          <Link to={ROUTES.reviews.edit(row.id)}>
            <Button color="primary" size="sm" className="h-auto py-1.5">
              Edit
            </Button>
          </Link>
        ),
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

      <DataTable<Review>
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
