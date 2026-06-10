import { apiClient, apiRequestRaw } from '@/api/apiService'
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

export const suburbsAPI = {
  fetch: (payload: FetchSuburbsPayload): Promise<FetchSuburbsResponse> =>
    apiRequestRaw(
      apiClient.post<FetchSuburbsResponse>(SUBURBS_ENDPOINTS.FETCH, payload)
    ),
}
