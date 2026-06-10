import { apiClient, apiRequestRaw } from '@/api/apiService'
import { REVIEWS_ENDPOINTS } from './reviewsAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

// ── Domain types ─────────────────────────────────────────────────────────────
// Shape mirrors what `/datatable-reviews` returns. The list endpoint is shared
// by the Approved (status=1) and Pending (status=0) tabs; the page picks which
// status to send in the request body.
//
// Fields outside of `id` / `title` / `status` / `recommendation` are typed as
// `unknown`-tolerant optionals because the legacy backend may include extra
// per-row metadata we haven't surfaced yet (e.g. content, rating, reviewer).

export interface Review {
  id: number
  title: string | null
  status: number
  // Backend may serialise this as a string label ("Recommend"/"Don't Recommend")
  // or as a 0/1 flag depending on the row. Accept both and let the cell
  // renderer normalise.
  recommendation: string | number | null
}

// ── Datatable filter ─────────────────────────────────────────────────────────
// `/datatable-reviews` extends the standard DataTables payload with a
// `status` field used to filter approved (1) vs pending (0) rows. The page
// owns which value to send.

export const REVIEW_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export type ReviewStatusFilter = (typeof REVIEW_STATUS)[keyof typeof REVIEW_STATUS]

export const reviewsAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  // Note the extra `status` field that filters approved vs pending rows.
  datatable: (
    payload: DataTablesRequest,
    status: ReviewStatusFilter
  ): Promise<DataTablesResponse<Review>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Review>>(
        REVIEWS_ENDPOINTS.DATATABLE,
        { ...payload, status }
      )
    ),
}
