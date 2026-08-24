import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { STORAGE_PATHS } from '@/constants/storagePaths'
import { COMPANIES_ENDPOINTS } from './companiesAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface Company {
  id: number
  company_name: string
  slug: string
  brand_type: string | null
  bio: string | null
  contact_number: string | null
  website: string | null
  logo: string | null
  logo_thumb: string | null
  location: string | null
  is_from: string | null
  status: number
  deleted_at: string | null
  created_at: string
  updated_at: string
  full_company_logo: string | null
}

export interface CompanyUser {
  id: number
  firstname: string | null
  lastname: string | null
  email: string
  company_id: number
  email_verified_at: string | null
  full_profile_image: string | null
  full_name: string
}

export interface CompanyDetail extends Company {
  company_users: CompanyUser[]
}

// `company-get` returns the non-standard `{ success, data }` shape.
// Kept raw so callers can inspect `.success` explicitly.
export interface GetCompanyResponse {
  success: boolean
  data: CompanyDetail
}

export interface CompanyDataTablesRequest extends DataTablesRequest {
  status: number
}

export interface UpdateCompanyStatusPayload {
  id: number
  status: number
}

export interface CreateCompanyPayload {
  company_name: string
  bio: string
  status: number
  contact_number: string
  website: string
  logo: File
}

export interface CompanyUserUpdate {
  id: number
  status: boolean
}

export interface UpdateCompanyPayload {
  id: number
  company_name: string
  bio: string
  status: number
  contact_number: string
  website: string
  logo: File | string
  users: CompanyUserUpdate[]
}

export const COMPANY_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export const companiesAPI = {
  getAll: (options?: ApiRequestOptions): Promise<Company[]> =>
    apiRequest<Company[]>(
      apiClient.get<ApiEnvelope<Company[]>>(COMPANIES_ENDPOINTS.GET_ALL),
      options
    ),

  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: CompanyDataTablesRequest
  ): Promise<DataTablesResponse<Company>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Company>>(
        COMPANIES_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  updateStatus: (
    payload: UpdateCompanyStatusPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        COMPANIES_ENDPOINTS.STATUS_UPDATE,
        payload
      ),
      options
    ),

  create: async (
    payload: CreateCompanyPayload,
    options?: ApiRequestOptions
  ): Promise<Company> => {
    const logoPath = await uploadAPI.uploadImage(
      payload.logo,
      STORAGE_PATHS.COMPANY_LOGO
    )

    return apiRequest<Company>(
      apiClient.post<ApiEnvelope<Company>>(COMPANIES_ENDPOINTS.CREATE, {
        company_name: payload.company_name,
        bio: payload.bio,
        status: payload.status,
        contact_number: payload.contact_number,
        website: payload.website,
        logo: logoPath,
      }),
      options
    )
  },

  // Non-standard `{ success, data }` shape — returned as-is.
  getById: (id: number): Promise<GetCompanyResponse> =>
    apiRequestRaw(
      apiClient.post<GetCompanyResponse>(COMPANIES_ENDPOINTS.GET, { id })
    ),

  delete: (id: number, options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(COMPANIES_ENDPOINTS.DELETE, { id }),
      options
    ),

  update: async (
    payload: UpdateCompanyPayload,
    options?: ApiRequestOptions
  ): Promise<Company> => {
    const logoPath =
      payload.logo instanceof File
        ? await uploadAPI.uploadImage(
            payload.logo,
            STORAGE_PATHS.COMPANY_LOGO
          )
        : payload.logo

    const fd = new FormData()
    fd.append('id', String(payload.id))
    fd.append('company_name', payload.company_name)
    fd.append('bio', payload.bio)
    fd.append('status', String(payload.status))
    fd.append('contact_number', payload.contact_number)
    fd.append('website', payload.website)
    fd.append('logo', logoPath)
    fd.append('users', JSON.stringify(payload.users))

    return apiRequest<Company>(
      apiClient.post<ApiEnvelope<Company>>(COMPANIES_ENDPOINTS.UPDATE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },
}
