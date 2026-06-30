import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
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
  // Backend serialises this as a boolean (true/false), but may also send a
  // string label ("Recommend"/"Don't Recommend") or a 0/1 flag on some rows.
  // Accept all and let the cell renderer normalise.
  recommendation: string | number | boolean | null
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

export interface UpdateReviewStatusPayload {
  id: number
  status: number
}

export interface UpdateReviewPayload {
  id: number
  title: string
  property_id: number
  recommendation: boolean
  comment: string
  buyer_type: string
  status: number
  state: number
  suburb: string
  post_code: string
  year_of_completion: string
  review_tags: number[]
}

export interface UpdateReviewResult {
  title: string
  property_id: number
  recommendation: boolean
  comment: string
  buyer_type: string
  status: number
  state: number
  suburb: string
  post_code: string
  year_of_completion: string
}

// ── Review detail (`/review/get/{id}`) ───────────────────────────────────────

export interface ReviewTag {
  id: number
  name: string
  type: string
  is_active: boolean
}

export interface ReviewSuburb {
  text: string
}

export interface ReviewPropertyOption {
  id: number
  project_name: string
  full_property_logo: string | null
}

export interface ReviewCompany {
  id: number
  company_name: string
  full_company_logo: string | null
}

export interface ReviewProperty {
  id: number
  project_name: string
  company: ReviewCompany | null
}

export interface ReviewUser {
  id: number
  display_name: string | null
  firstname: string | null
  lastname: string | null
  email: string
  email_verified_at: string | null
  full_profile_image: string | null
  full_name: string
}

export interface ReviewDetail {
  id: number
  property_id: number | null
  user_id: number | null
  category_id: number | null
  recommendation: boolean | number | null
  title: string | null
  comment: string | null
  slug: string | null
  review_image: string | null
  review_docs: string | null
  buyer_type: string | null
  state: number | string | null
  post_code: string | null
  suburb: string | null
  year_of_completion: string | null
  status: number
  suburb_list: ReviewSuburb[]
  tags_list: ReviewTag[]
  property_list: ReviewPropertyOption[]
  full_review_image: string | null
  full_review_docs?: string | null
  property: ReviewProperty | null
  users: ReviewUser | null
  review_tags: ReviewTag[]
}

// ── Review form metadata (`/buyer-type-get`) ─────────────────────────────────

export interface BuyerTypeOption {
  id: number
  select_id: number
  key_text: string
  value_text: string
}

export interface ReviewCategory {
  id: number
  name: string
  slug: string
}

export interface ReviewFormMeta {
  buyertype: {
    id: number
    name: string
    title: string
    option_select: BuyerTypeOption[]
  }
  categories: ReviewCategory[]
}

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

  updateStatus: (
    payload: UpdateReviewStatusPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        REVIEWS_ENDPOINTS.STATUS_UPDATE,
        payload
      ),
      options
    ),

  // DELETE /review/delete/{id} — no payload.
  delete: (id: number, options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.delete<ApiEnvelope<unknown>>(REVIEWS_ENDPOINTS.DELETE(id)),
      options
    ),

  getById: (id: number, options?: ApiRequestOptions): Promise<ReviewDetail> =>
    apiRequest<ReviewDetail>(
      apiClient.get<ApiEnvelope<ReviewDetail>>(REVIEWS_ENDPOINTS.GET(id)),
      options
    ),

  getFormMeta: (options?: ApiRequestOptions): Promise<ReviewFormMeta> =>
    apiRequest<ReviewFormMeta>(
      apiClient.get<ApiEnvelope<ReviewFormMeta>>(REVIEWS_ENDPOINTS.FORM_META),
      options
    ),

  update: (
    payload: UpdateReviewPayload,
    options?: ApiRequestOptions
  ): Promise<UpdateReviewResult> => {
    const fd = new FormData()
    fd.append('title', payload.title)
    fd.append('property_id', String(payload.property_id))
    fd.append('recommendation', payload.recommendation ? '1' : '0')
    fd.append('comment', payload.comment ?? '')
    fd.append('buyer_type', payload.buyer_type)
    fd.append('status', String(payload.status))
    fd.append('state', String(payload.state))
    fd.append('suburb', payload.suburb)
    fd.append('post_code', payload.post_code)
    fd.append('year_of_completion', payload.year_of_completion ?? '')
    payload.review_tags.forEach((id) => fd.append('review_tags[]', String(id)))
    if (payload.review_image instanceof File) {
      fd.append('review_image', payload.review_image)
    }
    if (payload.review_docs instanceof File) {
      fd.append('review_docs', payload.review_docs)
    }

    return apiRequest<UpdateReviewResult>(
      apiClient.post<ApiEnvelope<UpdateReviewResult>>(
        REVIEWS_ENDPOINTS.UPDATE(payload.id),
        fd,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      ),
      options
    )
  },
}
