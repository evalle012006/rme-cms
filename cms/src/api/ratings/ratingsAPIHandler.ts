import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { RATINGS_ENDPOINTS } from './ratingsAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface Rating {
  id: number
  name: string
  rating_icon: string | null
  description: string | null
  color_code: string | null
  deleted_at: string | null
  created_at: string
  updated_at: string
  full_ratingconf_icon: string | null
}

export interface CreateRatingPayload {
  name: string
  description: string
  rating_icon: File
}

export interface UpdateRatingPayload {
  id: number
  name: string
  description: string
  /** Include only when the user selects a new image file. */
  rating_icon?: File
}

// `get-rating` doesn't return the standard envelope — it returns
// `{ success: boolean, data: Rating }`. We keep it raw so callers can
// inspect `.success` explicitly. (Tracked for backend normalization.)
export interface GetRatingResponse {
  success: boolean
  data: Rating
}

export const ratingsAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<Rating>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Rating>>(
        RATINGS_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  // Non-standard `{ success, data }` shape — returned as-is.
  getById: (id: number): Promise<GetRatingResponse> =>
    apiRequestRaw(
      apiClient.post<GetRatingResponse>(RATINGS_ENDPOINTS.GET, { id })
    ),

  getConfiguration: (options?: ApiRequestOptions): Promise<Rating[]> =>
    apiRequest<Rating[]>(
      apiClient.get<ApiEnvelope<Rating[]>>(
        RATINGS_ENDPOINTS.GET_CONFIGURATION
      ),
      options
    ),

  create: (
    payload: CreateRatingPayload,
    options?: ApiRequestOptions
  ): Promise<Rating> => {
    const fd = new FormData()
    fd.append('name', payload.name)
    fd.append('description', payload.description)
    fd.append('rating_icon', payload.rating_icon)

    return apiRequest<Rating>(
      apiClient.post<ApiEnvelope<Rating>>(RATINGS_ENDPOINTS.CREATE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },

  update: (
    payload: UpdateRatingPayload,
    options?: ApiRequestOptions
  ): Promise<Rating> => {
    const fd = new FormData()
    fd.append('id', String(payload.id))
    fd.append('name', payload.name)
    fd.append('description', payload.description)
    if (payload.rating_icon instanceof File) {
      fd.append('rating_icon', payload.rating_icon)
    }

    return apiRequest<Rating>(
      apiClient.post<ApiEnvelope<Rating>>(RATINGS_ENDPOINTS.UPDATE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },

  delete: (id: number, options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(RATINGS_ENDPOINTS.DELETE, { id }),
      options
    ),
}
