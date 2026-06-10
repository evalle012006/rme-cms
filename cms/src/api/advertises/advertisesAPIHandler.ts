import { apiClient, apiRequestRaw } from '@/api/apiService'
import { ADVERTISES_ENDPOINTS } from './advertisesAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

// ── Domain types ─────────────────────────────────────────────────────────────
// Shape mirrors what `/datatable-advertises` returns. The list endpoint is
// shared by the Approved (status=1) and Pending (status=0) tabs; the page
// picks which status to send in the request body.
//
// The legacy column metadata addresses `company.company_name` via dotted
// notation, so we expose the joined company on the row type.

export interface AdvertiseCompany {
  id: number
  company_name: string
}

export interface Advertise {
  id: number
  title: string | null
  status: number
  company: AdvertiseCompany | null
}

// ── Datatable filter ─────────────────────────────────────────────────────────
// `/datatable-advertises` extends the standard DataTables payload with a
// `status` field used to filter approved (1) vs pending (0) rows. The page
// owns which value to send.

export const ADVERTISE_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export type AdvertiseStatusFilter =
  (typeof ADVERTISE_STATUS)[keyof typeof ADVERTISE_STATUS]

export const advertisesAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  // Note the extra `status` field that filters approved vs pending rows.
  datatable: (
    payload: DataTablesRequest,
    status: AdvertiseStatusFilter
  ): Promise<DataTablesResponse<Advertise>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Advertise>>(
        ADVERTISES_ENDPOINTS.DATATABLE,
        { ...payload, status }
      )
    ),
}
