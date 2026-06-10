import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { CATEGORIES_ENDPOINTS } from './categoriesAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'
import type { Rating } from '@/api/ratings/ratingsAPIHandler'

export interface PropertyCategory {
  id: number
  name: string
  slug: string
  image: string
  description: string
  question: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  deleted_at: string | null
  created_at: string
  updated_at: string
  full_cat_image: string
}

export interface PropertyAllowedRating extends Rating {
  pivot: {
    property_category_id: number
    rating_conf_id: number
    order: number | null
  }
}

export interface PropertyCategoryWithAllowedRatings extends PropertyCategory {
  property_allowed_rating: PropertyAllowedRating[]
}

// `category get` returns the non-standard `{ success, data }` shape.
// Kept raw so callers can inspect `.success` explicitly.
export interface GetPropertyCategoryResponse {
  success: boolean
  data: PropertyCategory
}

export interface CreatePropertyCategoryPayload {
  name: string
  description: string
  question: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  image: File
}

export interface UpdatePropertyCategoryPayload {
  id: number
  name: string
  description: string
  question: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  /** New upload, or existing storage path when unchanged. */
  image: File | string
}

export interface SetPropertyAllowedRatingsPayload {
  property_id: number
  rating_ids: number[]
}

export const categoriesAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<PropertyCategory>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<PropertyCategory>>(
        CATEGORIES_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  // Non-standard `{ success, data }` shape — returned as-is.
  getById: (id: number): Promise<GetPropertyCategoryResponse> =>
    apiRequestRaw(
      apiClient.post<GetPropertyCategoryResponse>(
        CATEGORIES_ENDPOINTS.GET,
        { id }
      )
    ),

  getAll: (options?: ApiRequestOptions): Promise<PropertyCategory[]> =>
    apiRequest<PropertyCategory[]>(
      apiClient.get<ApiEnvelope<PropertyCategory[]>>(
        CATEGORIES_ENDPOINTS.GET_ALL
      ),
      options
    ),

  create: async (
    payload: CreatePropertyCategoryPayload,
    options?: ApiRequestOptions
  ): Promise<PropertyCategory> => {
    const imagePath = await uploadAPI.uploadImage(payload.image)

    return apiRequest<PropertyCategory>(
      apiClient.post<ApiEnvelope<PropertyCategory>>(CATEGORIES_ENDPOINTS.ADD, {
        name: payload.name,
        description: payload.description,
        question: payload.question,
        seo_title: payload.seo_title,
        seo_description: payload.seo_description,
        seo_keywords: payload.seo_keywords,
        image: imagePath,
      }),
      options
    )
  },

  update: async (
    payload: UpdatePropertyCategoryPayload,
    options?: ApiRequestOptions
  ): Promise<PropertyCategory> => {
    const imagePath =
      payload.image instanceof File
        ? await uploadAPI.uploadImage(payload.image)
        : payload.image

    return apiRequest<PropertyCategory>(
      apiClient.post<ApiEnvelope<PropertyCategory>>(CATEGORIES_ENDPOINTS.UPDATE, {
        id: payload.id,
        name: payload.name,
        description: payload.description,
        question: payload.question,
        seo_title: payload.seo_title,
        seo_description: payload.seo_description,
        seo_keywords: payload.seo_keywords,
        image: imagePath,
      }),
      options
    )
  },

  getPropertiesWithAllowedRatings: (
    options?: ApiRequestOptions
  ): Promise<PropertyCategoryWithAllowedRatings[]> =>
    apiRequest<PropertyCategoryWithAllowedRatings[]>(
      apiClient.get<ApiEnvelope<PropertyCategoryWithAllowedRatings[]>>(
        CATEGORIES_ENDPOINTS.PROPERTIES_WITH_ALLOWED_RATINGS
      ),
      options
    ),

  // Returns plain `"success"` — not the standard envelope.
  setPropertyAllowedRatings: (
    payload: SetPropertyAllowedRatingsPayload
  ): Promise<string> =>
    apiRequestRaw<string>(
      apiClient.post<string>(CATEGORIES_ENDPOINTS.UPDATE_RATING, payload)
    ),
}
