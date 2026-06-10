import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { PROPERTIES_ENDPOINTS } from './propertiesAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'
import type { PropertyCategory } from '@/api/property-settings/categoriesAPIHandler'

// ── Domain types ─────────────────────────────────────────────────────────────
// Mirrors the row shape returned by `/datatable-property-pending` and
// `/datatable-property-approve`.
// `property_allowed_category` is the joined many-to-many with property
// categories (each row carries the pivot row alongside the category fields).

export interface PropertyAllowedCategory extends PropertyCategory {
  pivot: {
    property_id: number
    property_category_id: number
  }
}

export interface Property {
  id: number
  developer_name: number | null
  brand: string | null
  project_name: string
  logo: string | null
  slug: string
  email: string | null
  phone: string | null
  address: string | null
  country: string | null
  state: string | null
  city: string | null
  post_code: string | null
  suburb: string | null
  lat: string | number | null
  long: string | number | null
  year_of_completion: string | null
  buyer_type: string | null
  website_url: string | null
  description: string | null
  is_from: string | null
  status: number
  created_by: number | null
  owner: string | null
  deleted_at: string | null
  created_at: string
  updated_at: string
  full_property_logo: string | null
  property_allowed_category: PropertyAllowedCategory[]
}

// ── Status filter ────────────────────────────────────────────────────────────
// Property listing is split across two endpoints (one per status), so the
// shared list page picks the endpoint via this enum-like constant.

export const PROPERTY_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export type PropertyStatusFilter =
  (typeof PROPERTY_STATUS)[keyof typeof PROPERTY_STATUS]

// ── Create payload ───────────────────────────────────────────────────────────
// Mirrors the form on the Add/Edit Property pages. `property_categories` is
// sent as a JSON array. `state` is the state name (e.g. "Victoria"), not the id.

export interface CreatePropertyPayload {
  project_name: string
  developer_name: number | string
  status: number | string
  phone: string
  website_url: string
  description: string
  property_categories: number[]
  address: string
  country: string
  state: string
  city: string
  post_code: string
  suburb: string
  lat: string
  long: string
  logo: File
}

function appendPropertyFormFields(
  fd: FormData,
  payload: Omit<CreatePropertyPayload, 'logo'>
) {
  fd.append('project_name', payload.project_name)
  fd.append('developer_name', String(payload.developer_name))
  fd.append('status', String(payload.status))
  fd.append('phone', payload.phone)
  fd.append('website_url', payload.website_url)
  fd.append('description', payload.description)
  fd.append('address', payload.address)
  fd.append('country', payload.country)
  fd.append('state', payload.state)
  fd.append('city', payload.city)
  fd.append('post_code', payload.post_code)
  fd.append('suburb', payload.suburb)
  fd.append('lat', payload.lat)
  fd.append('long', payload.long)
  fd.append('property_categories', JSON.stringify(payload.property_categories))
}

export interface ApprovePropertyPayload {
  id: number
}

export interface DeletePropertyPayload {
  id: number
}

export interface UpdatePropertyPayload
  extends Omit<CreatePropertyPayload, 'logo'> {
  id: number
  logo: File | string | null
}

export const propertiesAPI = {
  // DataTables endpoint uses its own response shape (no envelope). The status
  // filter selects between two backend endpoints (approved vs pending lists).
  datatable: (
    payload: DataTablesRequest,
    status: PropertyStatusFilter
  ): Promise<DataTablesResponse<Property>> => {
    const endpoint =
      status === PROPERTY_STATUS.APPROVED
        ? PROPERTIES_ENDPOINTS.DATATABLE_APPROVED
        : PROPERTIES_ENDPOINTS.DATATABLE_PENDING
    return apiRequestRaw(
      apiClient.post<DataTablesResponse<Property>>(endpoint, payload)
    )
  },

  approve: (
    payload: ApprovePropertyPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        PROPERTIES_ENDPOINTS.APPROVE,
        payload
      ),
      options
    ),

  delete: (
    payload: DeletePropertyPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        PROPERTIES_ENDPOINTS.DELETE,
        payload
      ),
      options
    ),

  getById: (id: number, options?: ApiRequestOptions): Promise<Property> =>
    apiRequest<Property>(
      apiClient.get<ApiEnvelope<Property>>(
        `${PROPERTIES_ENDPOINTS.GET}/${id}`
      ),
      options
    ),

  create: async (
    payload: CreatePropertyPayload,
    options?: ApiRequestOptions
  ): Promise<Property> => {
    const logoPath = await uploadAPI.uploadImage(payload.logo)

    const fd = new FormData()
    appendPropertyFormFields(fd, payload)
    fd.append('logo', logoPath)
    return apiRequest<Property>(
      apiClient.post<ApiEnvelope<Property>>(PROPERTIES_ENDPOINTS.CREATE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },

  importFile: (
    file: File,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const fd = new FormData()
    fd.append('import_file', file)
    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(PROPERTIES_ENDPOINTS.IMPORT, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },

  update: async (
    payload: UpdatePropertyPayload,
    options?: ApiRequestOptions
  ): Promise<Property> => {
    const logoPath =
      payload.logo instanceof File
        ? await uploadAPI.uploadImage(payload.logo)
        : payload.logo

    const fd = new FormData()
    fd.append('id', String(payload.id))
    appendPropertyFormFields(fd, payload)
    if (logoPath !== null && logoPath !== undefined) {
      fd.append('logo', logoPath)
    }
    return apiRequest<Property>(
      apiClient.post<ApiEnvelope<Property>>(
        `${PROPERTIES_ENDPOINTS.UPDATE}/${payload.id}`,
        fd,
        {
          headers: { 'Content-Type': 'multipart/form-data' },
        }
      ),
      options
    )
  },
}
