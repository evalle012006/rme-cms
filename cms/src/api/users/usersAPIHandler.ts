import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { USERS_ENDPOINTS } from './usersAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'
import type { BrandType, UserStatus } from '@/api/users/usersConstants'

export interface User {
  id: number
  name: string
  firstname: string | null
  lastname: string | null
  display_name: string | null
  mobile: string | null
  email: string
  email_verified_at: string | null
  profile_image: string | null
  profile_thumb_image: string | null
  location: string | null
  latitude: string | null
  longitude: string | null
  brand_type: string | null
  company_id: number | null
  status: string
  has_change_request: number
  requested_display_name: string | null
  created_at: string
  updated_at: string
  full_profile_image: string | null
  full_name: string
}

// ── Get by id ────────────────────────────────────────────────────────────────
// `/user` — POST with `{ id }`. Backend returns the full user record (with
// the joined `roles` array). The same shape as the list row plus a few
// extras we don't currently consume.
//
// NOTE: this endpoint uses a non-standard `{ success, data }` shape (no
// `status` / `message` fields). We keep it raw so callers can inspect
// `.success` explicitly. Tracked for backend normalization.
export interface GetUserByIdResponse {
  success: boolean
  data: User
}

// ── Update reviewer ──────────────────────────────────────────────────────────
// `/auth/reviewer/update` — multipart/form-data. The backend mirrors the
// `null` / empty-string semantics of the legacy admin: nullable fields are
// sent as the literal string "null" when blank.

export interface UpdateReviewerPayload {
  id: number
  firstname: string
  lastname: string
  display_name: string
  email: string
  mobile: string | null
  location: string | null
  // Numeric status the API expects. "1" = Approved, "0" = Pending.
  status: string
  /** New upload as binary, or existing storage path when unchanged. */
  profile_image: File | string
}

// ── Create payloads ──────────────────────────────────────────────────────────

export interface CreateReviewerPayload {
  firstname: string
  lastname: string
  display_name: string
  email: string
  password: string
  mobile: string
  location: string
  status: UserStatus
  profile_image: File
}

export interface CreateBuilderPayload {
  firstname: string
  email: string
  password: string
  status: UserStatus
  brand_type: BrandType
  company_id: number
}

export const usersAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<User>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<User>>(
        USERS_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  createReviewer: async (
    payload: CreateReviewerPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const profileImagePath = await uploadAPI.uploadImage(payload.profile_image)

    const fd = new FormData()
    fd.append("firstname", payload.firstname)
    fd.append("lastname", payload.lastname)
    fd.append("display_name", payload.display_name)
    fd.append("email", payload.email)
    fd.append("password", payload.password)
    fd.append("mobile", payload.mobile)
    fd.append("location", payload.location)
    fd.append("status", payload.status)
    fd.append("profile_image", profileImagePath)
    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(USERS_ENDPOINTS.CREATE_REVIEWER, fd, {
        headers: { "Content-Type": "multipart/form-data" },
      }),
      options
    )
  },

  createBuilder: (
    payload: CreateBuilderPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const fd = new FormData()
    fd.append("firstname", payload.firstname)
    fd.append("email", payload.email)
    fd.append("password", payload.password)
    fd.append("status", payload.status)
    fd.append("brand_type", payload.brand_type)
    fd.append("company_id", String(payload.company_id))
    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(USERS_ENDPOINTS.CREATE_BUILDER, fd, {
        headers: { "Content-Type": "multipart/form-data" },
      }),
      options
    )
  },

  // Non-standard `{ success, data }` shape — returned as-is.
  getById: (id: number | string): Promise<GetUserByIdResponse> =>
    apiRequestRaw(
      apiClient.post<GetUserByIdResponse>(USERS_ENDPOINTS.GET_BY_ID, { id })
    ),

  updateReviewer: async (
    payload: UpdateReviewerPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const profileImagePath =
      payload.profile_image instanceof File
        ? await uploadAPI.uploadImage(payload.profile_image)
        : payload.profile_image

    const fd = new FormData()
    fd.append("id", String(payload.id))
    fd.append("firstname", payload.firstname)
    fd.append("lastname", payload.lastname)
    fd.append("display_name", payload.display_name)
    fd.append("email", payload.email)
    // Backend distinguishes "missing" from "cleared" by sending the literal
    // string "null" for nullable fields that the user left blank.
    fd.append("mobile", payload.mobile ?? "null")
    fd.append("location", payload.location ?? "null")
    fd.append("status", payload.status)
    fd.append("profile_image", profileImagePath)
    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(USERS_ENDPOINTS.UPDATE_REVIEWER, fd, {
        headers: { "Content-Type": "multipart/form-data" },
      }),
      options
    )
  },
}
