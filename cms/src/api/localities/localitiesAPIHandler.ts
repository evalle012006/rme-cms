import {
  apiClient,
  apiRequest,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { LOCALITIES_ENDPOINTS } from './localitiesAPIEndpoints'

/** One of the eight states and territories. */
export interface LocalityState {
  id: number
  /** Three letter code, e.g. "QLD". */
  code: string
  name: string
}

/**
 * A locality is one suburb + postcode + state combination. `label` is
 * disambiguated server-side, so it can be rendered as-is.
 */
export interface Locality {
  id: number
  suburb: string
  postcode: string
  state_code: string
  state_name: string
  label: string
  latitude: string | null
  longitude: string | null
}

/**
 * Bumps the browser cache key for these reads.
 *
 * Successful responses are cached publicly for a day. Copies stored before
 * they included Access-Control-Allow-Origin are reused by the admin site and
 * fail the CORS check until that entry expires. The server ignores this param.
 */
const CACHE_KEY = { v: '2' } as const

export const localitiesAPI = {
  states: (options?: ApiRequestOptions): Promise<LocalityState[]> =>
    apiRequest<LocalityState[]>(
      apiClient.get<ApiEnvelope<LocalityState[]>>(LOCALITIES_ENDPOINTS.STATES, {
        params: CACHE_KEY,
      }),
      options
    ),

  /** Every active locality in one state, alphabetical. */
  byState: (
    stateCode: string,
    options?: ApiRequestOptions
  ): Promise<Locality[]> =>
    apiRequest<Locality[]>(
      apiClient.get<ApiEnvelope<Locality[]>>(LOCALITIES_ENDPOINTS.LIST, {
        params: { state: stateCode, ...CACHE_KEY },
      }),
      options
    ),

  /** Keyword search, max 25 results. `stateCode` is optional. */
  search: (
    params: { q: string; stateCode?: string },
    options?: ApiRequestOptions
  ): Promise<Locality[]> =>
    apiRequest<Locality[]>(
      apiClient.get<ApiEnvelope<Locality[]>>(LOCALITIES_ENDPOINTS.SEARCH, {
        params: { q: params.q, state: params.stateCode || undefined, ...CACHE_KEY },
      }),
      options
    ),

  /** Every locality sharing a postcode — never reduced to one. */
  byPostcode: (
    postcode: string,
    options?: ApiRequestOptions
  ): Promise<Locality[]> =>
    apiRequest<Locality[]>(
      apiClient.get<ApiEnvelope<Locality[]>>(LOCALITIES_ENDPOINTS.BY_POSTCODE, {
        params: { postcode, ...CACHE_KEY },
      }),
      options
    ),
}
