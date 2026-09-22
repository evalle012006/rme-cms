import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { SUBURBS_ENDPOINTS } from './suburbsAPIEndpoints'

export interface SuburbOption {
  text: string
  id: string
}

export interface FetchSuburbsPayload {
  postcode: string
  q: string
}

// Backend returns `{ results: SuburbOption[] }` directly (not enveloped),
// so we use the raw wrapper.
export interface FetchSuburbsResponse {
  results: SuburbOption[]
}

export interface SuburbByStateOption {
  id: string
  name: string
}

export interface SuburbByStateWithPostcodeOption {
  id: string
  name: string
  postcode: string
}

export interface SuburbsByStatePayload {
  state_id: number | string
}

export const suburbsAPI = {
  fetch: (payload: FetchSuburbsPayload): Promise<FetchSuburbsResponse> =>
    apiRequestRaw(
      apiClient.post<FetchSuburbsResponse>(SUBURBS_ENDPOINTS.FETCH, payload)
    ),

  byState: (
    payload: SuburbsByStatePayload,
    options?: ApiRequestOptions
  ): Promise<SuburbByStateOption[]> =>
    apiRequest<SuburbByStateOption[]>(
      apiClient.post<ApiEnvelope<SuburbByStateOption[]>>(
        SUBURBS_ENDPOINTS.BY_STATE,
        payload
      ),
      options
    ),

  byStateWithPostcode: (
    payload: SuburbsByStatePayload,
    options?: ApiRequestOptions
  ): Promise<SuburbByStateWithPostcodeOption[]> =>
    apiRequest<SuburbByStateWithPostcodeOption[]>(
      apiClient.post<ApiEnvelope<SuburbByStateWithPostcodeOption[]>>(
        SUBURBS_ENDPOINTS.BY_STATE_WITH_POSTCODE,
        payload
      ),
      options
    ),
}
