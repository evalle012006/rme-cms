import {
  apiClient,
  apiRequest,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { DASHBOARD_ENDPOINTS } from './dashboardAPIEndpoints'

export interface DashboardCounters {
  company: number
  property: number
  review: number
  pending_review: number
  reviewer: number
}

export interface DashboardCompany {
  id: number
  name: string
  full_company_logo: string | null
}

export interface DashboardPropertyBar {
  id: number
  project_name: string
  overall_rating: number
  full_company_logo: string | null
  company_properties: Record<string, number> | unknown[]
}

export interface DashboardState {
  id: number
  name: string
}

export interface DashboardSuburb {
  id: number
  name: string
}

export interface DashboardPropertyOption {
  id: number
  name: string
  project_name?: string
  /** company_details.id — used to scope Rating Attributes project dropdown */
  developer_name?: number | string | null
}

export interface DashboardData {
  counters?: DashboardCounters
  total_users?: number
  total_reviews?: number
  total_properties?: number
  total_companies?: number
  recent_reviews?: unknown[]
  chart1?: { week_data: Record<string, number> }
  chart2?: { data: Record<string, number> }
  properties?: DashboardPropertyOption[]
  companies?: DashboardCompany[]
  properties_pie_chart?: unknown[]
  properties_bar_chart?: {
    data: DashboardPropertyBar[]
    total: number
  }
  options?: {
    state?: DashboardState[]
    suburb?: DashboardSuburb[]
    property?: DashboardPropertyOption[]
  }
  review_question?: Record<string, number | null>
}

export interface RatingAttributesFilterPayload {
  company_id: number | null
  project_id: number | null
  state: string
  suburb: string | null
}

export type RatingAttributesData = Record<string, number>

export interface PropertiesChartPayload {
  data: { year: number }
}

export interface PropertiesChartResponse {
  data?: Record<string, number>
  properties_pie_chart?: unknown[]
}

export interface ReviewCountChartPayload {
  start: string
  end: string
}

export type ReviewCountChartResponse = Record<string, number>

export type PropertyReviewsChartMode = 'first' | 'last'

export interface GetPropertyOptionsPayload {
  company_id?: number | null
  mode: PropertyReviewsChartMode
  state: string | null
  suburb: string | null
}

export interface GetPropertyOptionsResponse {
  data?: DashboardPropertyBar[]
  companies?: DashboardCompany[]
  states?: DashboardState[]
  suburbs?: DashboardSuburb[]
  properties?: DashboardPropertyOption[]
  properties_bar_chart?: {
    data: DashboardPropertyBar[]
    total: number
  }
  options?: {
    state?: DashboardState[]
    suburb?: DashboardSuburb[]
    property?: DashboardPropertyOption[]
  }
}

export function extractPropertyReviewsChartData(
  response: GetPropertyOptionsResponse
): DashboardPropertyBar[] {
  if (Array.isArray(response.data)) return response.data
  return response.properties_bar_chart?.data ?? []
}

export interface DashboardExportCsvResponse {
  url: string
}

export function normalizeDashboardCounters(
  data: DashboardData
): DashboardCounters {
  if (data.counters) {
    return {
      company: data.counters.company ?? 0,
      property: data.counters.property ?? 0,
      review: data.counters.review ?? 0,
      pending_review: data.counters.pending_review ?? 0,
      reviewer: data.counters.reviewer ?? 0,
    }
  }

  return {
    company: data.total_companies ?? 0,
    property: data.total_properties ?? 0,
    review: data.total_reviews ?? 0,
    pending_review: 0,
    reviewer: data.total_users ?? 0,
  }
}

export const dashboardAPI = {
  get: (options?: ApiRequestOptions): Promise<DashboardData> =>
    apiRequest<DashboardData>(
      apiClient.post<ApiEnvelope<DashboardData>>(DASHBOARD_ENDPOINTS.GET, {}),
      options
    ),

  filterRatingAttributesChart: (
    payload: RatingAttributesFilterPayload,
    options?: ApiRequestOptions
  ): Promise<RatingAttributesData> =>
    apiRequest<RatingAttributesData>(
      apiClient.post<ApiEnvelope<RatingAttributesData>>(
        DASHBOARD_ENDPOINTS.FILTER_RATING_CHART,
        payload
      ),
      options
    ),

  propertiesChart: (
    payload: PropertiesChartPayload,
    options?: ApiRequestOptions
  ): Promise<PropertiesChartResponse> =>
    apiRequest<PropertiesChartResponse>(
      apiClient.post<ApiEnvelope<PropertiesChartResponse>>(
        DASHBOARD_ENDPOINTS.PROPERTIES_CHART,
        payload
      ),
      options
    ),

  reviewCountChart: (
    payload: ReviewCountChartPayload,
    options?: ApiRequestOptions
  ): Promise<ReviewCountChartResponse> =>
    apiRequest<ReviewCountChartResponse>(
      apiClient.post<ApiEnvelope<ReviewCountChartResponse>>(
        DASHBOARD_ENDPOINTS.REVIEW_COUNT_CHART,
        payload
      ),
      options
    ),

  getPropertyOptions: (
    payload: GetPropertyOptionsPayload,
    options?: ApiRequestOptions
  ): Promise<GetPropertyOptionsResponse> =>
    apiRequest<GetPropertyOptionsResponse>(
      apiClient.post<ApiEnvelope<GetPropertyOptionsResponse>>(
        DASHBOARD_ENDPOINTS.GET_PROPERTY_OPTIONS,
        payload
      ),
      options
    ),

  exportCsv: (
    options?: ApiRequestOptions
  ): Promise<DashboardExportCsvResponse> =>
    apiRequest<DashboardExportCsvResponse>(
      apiClient.get<ApiEnvelope<DashboardExportCsvResponse>>(
        DASHBOARD_ENDPOINTS.EXPORT_CSV
      ),
      options
    ),
}
