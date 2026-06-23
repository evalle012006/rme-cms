import {
  apiClient,
  apiRequest,
  apiRequestRaw,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { BLOGS_ENDPOINTS } from './blogsAPIEndpoints'
import type { BlogCategory } from '@/api/blogCategories/blogCategoriesAPIHandler'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface BlogCategoryPivot {
  blog_id: number
  blog_category_id: number
}

export interface BlogCategoryRelation extends BlogCategory {
  pivot: BlogCategoryPivot
}

export interface Blog {
  id: number
  author_name: string | null
  title: string
  slug: string
  feature_image: string
  feature_thumb_image: string | null
  blog_date: string
  description: string
  status: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  category_id: number
  created_by: number
  created_at: string
  updated_at: string
  deleted_at: string | null
  full_feature_image: string
  blog_categories: BlogCategoryRelation[]
}

export interface CreateBlogPayload {
  title: string
  feature_image: File
  description: string
  blog_date: string
  status: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  category_id: number
  author_name: string
  created_by: number
}

export interface UpdateBlogPayload {
  id: number
  title: string
  feature_image: File | string
  description: string
  blog_date: string
  status: string
  seo_title: string
  seo_description: string
  seo_keywords: string
  category_id: number
  author_name: string
}

export const BLOG_STATUS = {
  PUBLISHED: '0',
  DRAFT: '1',
  ARCHIVE: '2',
} as const

export const blogsAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<Blog>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<Blog>>(
        BLOGS_ENDPOINTS.DATATABLE,
        payload
      )
    ),

  getById: (id: number, options?: ApiRequestOptions): Promise<Blog> =>
    apiRequest<Blog>(
      apiClient.get<ApiEnvelope<Blog>>(`${BLOGS_ENDPOINTS.GET}/${id}`),
      options
    ),

  create: async (
    payload: CreateBlogPayload,
    options?: ApiRequestOptions
  ): Promise<Blog> => {
    const featureImagePath = await uploadAPI.uploadImage(payload.feature_image)

    return apiRequest<Blog>(
      apiClient.post<ApiEnvelope<Blog>>(BLOGS_ENDPOINTS.ADD, {
        title: payload.title,
        feature_image: featureImagePath,
        description: payload.description,
        blog_date: payload.blog_date,
        status: payload.status,
        seo_title: payload.seo_title,
        seo_description: payload.seo_description,
        seo_keywords: payload.seo_keywords,
        category_id: payload.category_id,
        author_name: payload.author_name,
        created_by: payload.created_by,
      }),
      options
    )
  },

  update: async (
    payload: UpdateBlogPayload,
    options?: ApiRequestOptions
  ): Promise<Blog> => {
    const featureImagePath =
      payload.feature_image instanceof File
        ? await uploadAPI.uploadImage(payload.feature_image)
        : payload.feature_image

    const fd = new FormData()
    fd.append('id', String(payload.id))
    fd.append('title', payload.title)
    fd.append('feature_image', featureImagePath)
    fd.append('description', payload.description)
    fd.append('blog_date', payload.blog_date)
    fd.append('status', payload.status)
    fd.append('seo_title', payload.seo_title)
    fd.append('seo_description', payload.seo_description)
    fd.append('seo_keywords', payload.seo_keywords)
    fd.append('category_id', String(payload.category_id))
    fd.append('author_name', payload.author_name)

    return apiRequest<Blog>(
      apiClient.post<ApiEnvelope<Blog>>(BLOGS_ENDPOINTS.UPDATE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },

  delete: (id: number, options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(BLOGS_ENDPOINTS.DELETE, { id }),
      options
    ),
}
