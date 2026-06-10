import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { BLOG_CATEGORIES_ENDPOINTS } from './blogCategoriesAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface BlogCategory {
  id: number
  name: string
  slug: string
  image: string
  seo_title: string | null
  seo_description: string | null
  seo_keywords: string | null
  created_at: string
  updated_at: string
  deleted_at: string | null
  full_blog_category_image: string
}

export interface CreateBlogCategoryPayload {
  name: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  image: File
}

export interface UpdateBlogCategoryPayload {
  id: number
  name: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  /** New upload as binary, or existing storage path when unchanged. */
  image: File | string
}

export const blogCategoriesAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<BlogCategory>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<BlogCategory>>(
        BLOG_CATEGORIES_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  getById: (
    id: number,
    options?: ApiRequestOptions
  ): Promise<BlogCategory> =>
    apiRequest<BlogCategory>(
      apiClient.get<ApiEnvelope<BlogCategory>>(
        `${BLOG_CATEGORIES_ENDPOINTS.GET}/${id}`
      ),
      options
    ),

  getAll: (options?: ApiRequestOptions): Promise<BlogCategory[]> =>
    apiRequest<BlogCategory[]>(
      apiClient.get<ApiEnvelope<BlogCategory[]>>(
        BLOG_CATEGORIES_ENDPOINTS.GET_ALL
      ),
      options
    ),

  create: async (
    payload: CreateBlogCategoryPayload,
    options?: ApiRequestOptions
  ): Promise<BlogCategory> => {
    const imagePath = await uploadAPI.uploadImage(payload.image)

    return apiRequest<BlogCategory>(
      apiClient.post<ApiEnvelope<BlogCategory>>(BLOG_CATEGORIES_ENDPOINTS.ADD, {
        name: payload.name,
        seo_title: payload.seo_title,
        seo_description: payload.seo_description,
        seo_keywords: payload.seo_keywords,
        image: imagePath,
      }),
      options
    )
  },

  update: async (
    payload: UpdateBlogCategoryPayload,
    options?: ApiRequestOptions
  ): Promise<BlogCategory> => {
    const imagePath =
      payload.image instanceof File
        ? await uploadAPI.uploadImage(payload.image)
        : payload.image

    const fd = new FormData()
    fd.append('id', String(payload.id))
    fd.append('name', payload.name)
    fd.append('image', imagePath)
    fd.append('seo_title', payload.seo_title)
    fd.append('seo_description', payload.seo_description)
    fd.append('seo_keywords', payload.seo_keywords)

    return apiRequest<BlogCategory>(
      apiClient.post<ApiEnvelope<BlogCategory>>(
        BLOG_CATEGORIES_ENDPOINTS.UPDATE,
        fd,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      ),
      options
    )
  },
}
