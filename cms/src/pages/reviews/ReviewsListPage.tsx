import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Link } from "react-router-dom"
import { EyeOff, Trash2 } from "lucide-react"
import { toast } from "sonner"
import { Button } from "@/components/atoms/Button"
import { Breadcrumb, type BreadcrumbItem } from "@/components/atoms/Breadcrumb"
import { ConfirmDialog } from "@/components/atoms/ConfirmDialog"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { StatusBadge, type StatusTone } from "@/components/atoms/StatusBadge"
import {
  reviewsAPI,
  REVIEW_STATUS,
  type Review,
  type ReviewLocalityFilter,
  type ReviewStatusFilter,
} from "@/api/reviews/reviewsAPIHandler"
import { DeleteReviewDialog } from "./DeleteReviewDialog"
import {
  EMPTY_LOCALITY_SELECTION,
  LocalitySelect,
  type LocalitySelection,
} from "@/components/atoms/LocalitySelect"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"
import { ROUTES } from "@/constants/routes"
import { formatDate } from "@/lib/utils"

// ── Column metadata for `/datatable-reviews` ────────────────────────────────
// Index order mirrors what the backend expects. Title and created_at are
// searchable/orderable as marked. The 4th column has a deliberately empty
// `name` — the legacy payload sends it that way, so we preserve it here to
// keep the request shape identical to the production app.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "title",          searchable: true,  orderable: true  },
  { name: "status",         searchable: false, orderable: false },
  { name: "recommendation", searchable: false, orderable: false },
  { name: "",               searchable: false, orderable: false },
  { name: "action",         searchable: false, orderable: false },
  { name: "created_at",     searchable: false, orderable: true  },
]

// ── Recommendation cell ──────────────────────────────────────────────────────
// The backend may send either a string label or a numeric 0/1 flag depending
// on the row. Normalise to a yes/no flag so we can render a coloured badge.
function isRecommended(value: Review["recommendation"]): boolean | null {
  if (value === null || value === undefined || value === "") return null
  if (typeof value === "boolean") return value
  if (typeof value === "number") return value === 1
  if (typeof value === "string") {
    const normalised = value.trim().toLowerCase()
    if (/^(don'?t|no|0)/.test(normalised)) return false
    if (/^(rec|yes|1)/.test(normalised)) return true
  }
  return null
}

// ── Shared list page ─────────────────────────────────────────────────────────
// Powers the Approved, Pending and Inactive tabs of "Reviews". The pages
// differ only by breadcrumb, status filter (1 / 0 / 2), the status badge
// label/tone, the status the badge flips to when clicked, and whether a
// "Deactivate" action is offered.

const FROM_HINT: Record<ReviewStatusFilter, string> = {
  [REVIEW_STATUS.APPROVED]: "approved",
  [REVIEW_STATUS.PENDING]: "pending",
  [REVIEW_STATUS.INACTIVE]: "inactive",
}

interface ReviewsListPageProps {
  breadcrumb: BreadcrumbItem[]
  statusFilter: ReviewStatusFilter
  statusLabel: string
  statusTone: StatusTone
  // Status to set via `/review/change-status` when the badge is clicked, plus
  // the confirm/success copy. Pending → 1 (approve), Approved → 0 (pending),
  // Inactive → 1 (reactivate).
  flipStatusTo: number
  flipConfirmMessage: string
  flipSuccessMessage: string
  // Offer a per-row "Deactivate" action (sets status 2). Inactive reviews
  // stay in admin but are hidden from the site and excluded from ratings.
  allowDeactivate?: boolean
}

export function ReviewsListPage({
  breadcrumb,
  statusFilter,
  statusLabel,
  statusTone,
  flipStatusTo,
  flipConfirmMessage,
  flipSuccessMessage,
  allowDeactivate = false,
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
  const [confirmId, setConfirmId] = useState<number | null>(null)
  const [reviewToDeactivate, setReviewToDeactivate] = useState<Review | null>(null)
  const [reviewToDelete, setReviewToDelete] = useState<Review | null>(null)
  const [locality, setLocality] = useState<LocalitySelection>(
    EMPTY_LOCALITY_SELECTION
  )
  const [reportLoading, setReportLoading] = useState(false)

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

    const localityFilter: ReviewLocalityFilter = {}
    if (locality.localityId) {
      localityFilter.locality_id = Number(locality.localityId)
    }
    if (locality.stateCode) {
      localityFilter.state_code = locality.stateCode
    }
    if (locality.postcode) {
      localityFilter.postcode = locality.postcode
    }

    reviewsAPI
      .datatable(payload, statusFilter, localityFilter)
      .then((res) => {
        if (Number(res.draw) !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir, statusFilter, locality])

  // Reset page/sort/search when the tab (status filter) changes.
  useEffect(() => {
    setPage(1)
    setSortKey(undefined)
    setSortDir(undefined)
    setSearch("")
    setLocality(EMPTY_LOCALITY_SELECTION)
  }, [statusFilter])

  useEffect(() => {
    fetchReviews()
  }, [fetchReviews])

  const confirmStatusFlip = useCallback(async () => {
    if (confirmId === null) return
    try {
      await reviewsAPI.updateStatus(
        { id: confirmId, status: flipStatusTo },
        { successMessage: flipSuccessMessage }
      )
      fetchReviews()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [confirmId, flipStatusTo, flipSuccessMessage, fetchReviews])

  const confirmDeactivate = useCallback(async () => {
    if (reviewToDeactivate === null) return
    try {
      await reviewsAPI.updateStatus(
        { id: reviewToDeactivate.id, status: REVIEW_STATUS.INACTIVE },
        { successMessage: "Review set to inactive" }
      )
      fetchReviews()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [reviewToDeactivate, fetchReviews])

  const fromHint = FROM_HINT[statusFilter]

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
        key: "project",
        header: "Project",
        cell: (row) => row.property?.project_name || "—",
      },
      {
        key: "company",
        header: "Company",
        cell: (row) => row.property?.company?.company_name || "—",
      },
      {
        key: "created_at",
        header: "Submitted At",
        width: "140px",
        sortable: true,
        cell: (row) => formatDate(row.created_at),
      },
      {
        key: "status",
        header: "Status",
        width: "160px",
        cell: (row) => (
          <StatusBadge
            label={statusLabel}
            tone={statusTone}
            onClick={() => setConfirmId(row.id)}
          />
        ),
      },
      {
        key: "recommendation",
        header: "Recommendation",
        cell: (row) => {
          const recommended = isRecommended(row.recommendation)
          if (recommended === null) return <span className="text-gray-400">—</span>
          return (
            <StatusBadge
              label={recommended ? "Yes" : "No"}
              tone={recommended ? "approved" : "pending"}
            />
          )
        },
      },
      {
        key: "action",
        header: "Action",
        width: allowDeactivate ? "190px" : "140px",
        cell: (row) => (
          <div className="flex items-center gap-2">
            <Link to={`${ROUTES.reviews.edit(row.id)}?from=${fromHint}`}>
              <Button color="primary" size="sm" className="h-auto py-1.5">
                Edit
              </Button>
            </Link>
            {allowDeactivate && (
              <Button
                type="button"
                color="secondary"
                size="sm"
                className="h-auto px-2 py-1.5"
                aria-label="Set review inactive"
                title="Set inactive (hide from site and ratings)"
                onClick={() => setReviewToDeactivate(row)}
              >
                <EyeOff size={16} />
              </Button>
            )}
            <Button
              type="button"
              color="destructive"
              size="sm"
              className="h-auto px-2 py-1.5"
              aria-label="Delete review"
              title="Delete review"
              onClick={() => setReviewToDelete(row)}
            >
              <Trash2 size={16} />
            </Button>
          </div>
        ),
      },
    ],
    [statusLabel, statusTone, fromHint, allowDeactivate]
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

  const hasLocalityFilter = Boolean(
    locality.stateCode || locality.localityId || locality.postcode
  )

  // Reviews submitted before locations were controlled may have no locality, so
  // they cannot be filtered. The report lists them for manual resolution.
  const downloadLocalityExceptions = useCallback(async () => {
    setReportLoading(true)
    try {
      const report = await reviewsAPI.localityExceptions({
        customErrorMessage: "Could not build the unresolved locations report",
      })
      if (!report.url) {
        toast.success("Every review has a resolved location")
        return
      }
      window.open(report.url, "_blank", "noopener")
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setReportLoading(false)
    }
  }, [])

  // Derived pagination fields from total + page + perPage.
  const lastPage = Math.max(1, Math.ceil(total / perPage))
  const from = total === 0 ? null : (page - 1) * perPage + 1
  const to = total === 0 ? null : Math.min(page * perPage, total)

  return (
    <div className="space-y-10">
      <Breadcrumb items={breadcrumb} />

      <div className="flex flex-col gap-4 rounded-card border border-gray-200 bg-white p-5 lg:flex-row lg:items-end">
        <LocalitySelect
          className="flex-1"
          value={locality}
          onChange={(next) => {
            setLocality(next)
            setPage(1)
          }}
        />
        <Button
          type="button"
          color="secondary"
          className="h-auto py-2.5 whitespace-nowrap"
          disabled={!hasLocalityFilter}
          onClick={() => {
            setLocality(EMPTY_LOCALITY_SELECTION)
            setPage(1)
          }}
        >
          Clear all filters
        </Button>
        <Button
          type="button"
          color="secondary"
          className="h-auto py-2.5 whitespace-nowrap"
          disabled={reportLoading}
          onClick={() => void downloadLocalityExceptions()}
        >
          {reportLoading ? "Preparing…" : "Unresolved locations"}
        </Button>
      </div>

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

      <ConfirmDialog
        open={reviewToDeactivate !== null}
        onOpenChange={(next) => {
          if (!next) setReviewToDeactivate(null)
        }}
        title="Set review inactive?"
        description={
          <>
            {reviewToDeactivate?.title
              ? `"${reviewToDeactivate.title}" `
              : "This review "}
            will be hidden from the site and excluded from ratings. It stays
            here in admin and can be reactivated from the Inactive list.
          </>
        }
        tone="warning"
        confirmLabel="Set inactive"
        cancelLabel="Cancel"
        onConfirm={confirmDeactivate}
      />

      <DeleteReviewDialog
        review={reviewToDelete}
        onClose={() => setReviewToDelete(null)}
        onDeleted={fetchReviews}
      />
    </div>
  )
}
