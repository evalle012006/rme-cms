import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { REVIEWS_ENDPOINTS } from './reviewsAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface ReviewTag {
  id: number
  name: string
  type: string
}

export interface ReviewProperty {
  id: number
  project_name: string
  developer_name: number | null
  company: { id: number; company_name: string; is_from: string } | null
}

export interface ReviewUser {
  id: number
  display_name: string | null
  firstname: string | null
  lastname: string | null
  email: string
}

export interface ReviewDetail {
  id: number
  property_id: number
  user_id: number
  category_id: number
  title: string | null
  comment: string | null
  buyer_type: number | null
  recommendation: boolean | null
  state: string | null
  suburb: string | null
  post_code: string | null
  year_of_completion: number | null
  status: number
  review_image: string | null
  review_docs: string | null
  full_review_image: string | null
  created_at: string
  updated_at: string
  property: ReviewProperty | null
  users: ReviewUser | null
  reviewTags: ReviewTag[]
  suburb_list: { text: string }[]
  tags_list: ReviewTag[]
  property_list: { id: number; project_name: string }[]
}

export interface BuyerTypeOption {
  id: number
  name: string
}

export interface ReviewCategory {
  id: number
  name: string
  slug: string
}

export interface BuyerTypesResponse {
  buyertype: { option_select: BuyerTypeOption[] }
  categories: ReviewCategory[]
}

export interface UpdateReviewPayload {
  title: string
  comment: string | null
  buyer_type: number
  recommendation: boolean | number
  status: number
  state: string
  suburb: string
  post_code: string
  year_of_completion: number | null
  property_id: number
  category_id: number
  review_tags: { id: number; name: string }[]
}

export interface Review {
  id: number
  title: string | null
  status: number
  recommendation: string | number | null
}

export const REVIEW_STATUS = {
  PENDING: 0,
  APPROVED: 1,
} as const

export type ReviewStatusFilter = (typeof REVIEW_STATUS)[keyof typeof REVIEW_STATUS]

export const reviewsAPI = {
  datatable: (
    payload: DataTablesRequest,
    status: ReviewStatusFilter
  ): Promise<DataTablesResponse<Review>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Review>>(
        REVIEWS_ENDPOINTS.DATATABLE,
        { ...payload, status }
      )
    ),

  getById: (id: number | string): Promise<ReviewDetail> =>
    apiRequest<ReviewDetail>(
      apiClient.get<ApiEnvelope<ReviewDetail>>(
        REVIEWS_ENDPOINTS.GET_BY_ID.replace(':id', String(id))
      )
    ),

  update: (
    id: number | string,
    payload: UpdateReviewPayload,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(
        REVIEWS_ENDPOINTS.UPDATE.replace(':id', String(id)),
        payload
      ),
      options
    ),

  changeStatus: (
    id: number,
    status: ReviewStatusFilter,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(REVIEWS_ENDPOINTS.CHANGE_STATUS, {
        id,
        status,
      }),
      options
    ),

  delete: (
    id: number,
    options?: ApiRequestOptions
  ): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.delete<ApiEnvelope<unknown>>(
        REVIEWS_ENDPOINTS.DELETE.replace(':id', String(id))
      ),
      options
    ),

  getBuyerTypes: (options?: ApiRequestOptions): Promise<BuyerTypesResponse> =>
    apiRequest<BuyerTypesResponse>(
      apiClient.get<ApiEnvelope<BuyerTypesResponse>>(
        REVIEWS_ENDPOINTS.BUYER_TYPES
      ),
      options
    ),

  getStates: (options?: ApiRequestOptions): Promise<{ id: number; state_name: string }[]> =>
    apiRequest<{ id: number; state_name: string }[]>(
      apiClient.get<ApiEnvelope<{ id: number; state_name: string }[]>>(
        REVIEWS_ENDPOINTS.STATES
      ),
      options
    ),

  getSuburbs: (
    post_code: string,
    options?: ApiRequestOptions
  ): Promise<{ text: string }[]> =>
    apiRequest<{ text: string }[]>(
      apiClient.post<ApiEnvelope<{ text: string }[]>>(
        REVIEWS_ENDPOINTS.SUBURBS,
        { post_code }
      ),
      options
    ),
}