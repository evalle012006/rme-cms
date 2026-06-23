import {
  apiClient,
  apiRequest,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { STATES_ENDPOINTS } from './statesAPIEndpoints'

export interface State {
  id: number
  state_name: string
}

export const statesAPI = {
  getAll: (options?: ApiRequestOptions): Promise<State[]> =>
    apiRequest<State[]>(
      apiClient.get<ApiEnvelope<State[]>>(STATES_ENDPOINTS.GET_ALL),
      options
    ),
}
