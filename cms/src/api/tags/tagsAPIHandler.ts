import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { TAGS_ENDPOINTS } from './tagsAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface Tag {
  id: number
  name: string
  type: string
  is_active: boolean
  created_at: string | null
  updated_at: string | null
  deleted_at: string | null
}

export interface CreateTagPayload {
  name: string
  type: string
  is_active: boolean
}

export interface UpdateTagPayload extends CreateTagPayload {
  id: number
}

export const tagsAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<Tag>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Tag>>(
        TAGS_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  getById: (id: number, options?: ApiRequestOptions): Promise<Tag> =>
    apiRequest<Tag>(
      apiClient.get<ApiEnvelope<Tag>>(`${TAGS_ENDPOINTS.GET}/${id}`),
      options
    ),

  add: (
    payload: CreateTagPayload,
    options?: ApiRequestOptions
  ): Promise<Tag> =>
    apiRequest<Tag>(
      apiClient.post<ApiEnvelope<Tag>>(TAGS_ENDPOINTS.ADD, payload),
      options
    ),

  update: (
    payload: UpdateTagPayload,
    options?: ApiRequestOptions
  ): Promise<Tag> =>
    apiRequest<Tag>(
      apiClient.post<ApiEnvelope<Tag>>(TAGS_ENDPOINTS.UPDATE, payload),
      options
    ),
}
