import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { BUILDERS_ENDPOINTS } from './buildersAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

// ── Domain types ─────────────────────────────────────────────────────────────

export interface BuilderCompany {
  id: number
  company_name: string
  full_company_logo: string | null
  // Extra fields returned by /builder/get-by-id (not present on the list
  // endpoint) — optional so the same type can be reused in both places.
  bio?: string | null
  logo?: string | null
  website?: string | null
}

export interface Builder {
  id: number
  company_id: number | null
  email: string
  firstname: string | null
  lastname: string | null
  email_verified_at: string | null
  status: string | null
  full_profile_image: string | null
  full_name: string
  company: BuilderCompany | null
}

// ── Datatable filter ─────────────────────────────────────────────────────────
// `/datatable-builders` extends the standard DataTables payload with a
// `status` field used to filter approved (1) vs pending (0) rows. The page
// owns which value to send.

export const BUILDER_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export type BuilderStatusFilter = (typeof BUILDER_STATUS)[keyof typeof BUILDER_STATUS]

export const BUILDER_STATUS_OPTIONS: { value: string; label: string }[] = [
  { value: String(BUILDER_STATUS.APPROVED), label: "Approved" },
  { value: String(BUILDER_STATUS.PENDING), label: "Pending" },
]

export interface ChangeBuilderStatusPayload {
  id: number
  status: BuilderStatusFilter
}

export interface UpdateBuilderPayload {
  id: number
  company_id: number
  firstname: string
  lastname: string
  email: string
  status: BuilderStatusFilter
  company_name: string
  company_bio: string
  website: string
  company_logo: File | string | null
}

export const buildersAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  // Note the extra `status` field that filters approved vs pending rows.
  datatable: (
    payload: DataTablesRequest,
    status: BuilderStatusFilter
  ): Promise<DataTablesResponse<Builder>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Builder>>(
        BUILDERS_ENDPOINTS.DATATABLE,
        { ...payload, status }
      )
    ),

  changeStatus: (
    id: number,
    status: BuilderStatusFilter,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        BUILDERS_ENDPOINTS.CHANGE_STATUS,
        { id, status } satisfies ChangeBuilderStatusPayload
      ),
      options
    ),

  getById: (
    id: number | string,
    options?: ApiRequestOptions
  ): Promise<Builder> =>
    apiRequest<Builder>(
      apiClient.post<ApiEnvelope<Builder>>(BUILDERS_ENDPOINTS.GET_BY_ID, {
        // Backend expects a stringified id.
        id: String(id),
      }),
      options
    ),

  update: async (
    payload: UpdateBuilderPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const companyLogoPath =
      payload.company_logo instanceof File
        ? await uploadAPI.uploadImage(payload.company_logo)
        : payload.company_logo

    const fd = new FormData()
    fd.append("id", String(payload.id))
    fd.append("company_id", String(payload.company_id))
    fd.append("firstname", payload.firstname)
    fd.append("lastname", payload.lastname)
    fd.append("email", payload.email)
    fd.append("status", String(payload.status))
    fd.append("company_name", payload.company_name)
    fd.append("company_bio", payload.company_bio)
    fd.append("website", payload.website)
    if (companyLogoPath !== null && companyLogoPath !== undefined) {
      fd.append("company_logo", companyLogoPath)
    }
    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(BUILDERS_ENDPOINTS.UPDATE, fd, {
        headers: { "Content-Type": "multipart/form-data" },
      }),
      options
    )
  },
}
