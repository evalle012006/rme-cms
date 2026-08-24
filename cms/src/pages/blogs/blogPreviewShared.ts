import type { Blog } from '@/api/blogs/blogsAPIHandler'
import { BLOG_STATUS } from '@/api/blogs/blogsAPIHandler'
import { formatDate, imgUrl } from '@/lib/utils'

export const BLOG_PREVIEW_STORAGE_KEY = 'cms-blog-preview'

export interface BlogPreviewData {
  title: string
  description: string
  blog_date: string
  author_name: string
  status: string
  category_name: string
  feature_image_url: string | null
  author_image_url: string | null
  return_to?: string
}

export function stashBlogPreview(data: BlogPreviewData): void {
  sessionStorage.setItem(BLOG_PREVIEW_STORAGE_KEY, JSON.stringify(data))
}

export function readStashedBlogPreview(): BlogPreviewData | null {
  try {
    const raw = sessionStorage.getItem(BLOG_PREVIEW_STORAGE_KEY)
    if (!raw) return null
    return JSON.parse(raw) as BlogPreviewData
  } catch {
    return null
  }
}

export function clearStashedBlogPreview(): void {
  sessionStorage.removeItem(BLOG_PREVIEW_STORAGE_KEY)
}

export function blogToPreviewData(blog: Blog): BlogPreviewData {
  return {
    title: blog.title,
    description: blog.description ?? '',
    blog_date: blog.blog_date,
    author_name: blog.author_name ?? '',
    status: blog.status,
    category_name: blog.blog_categories?.[0]?.name ?? '',
    feature_image_url: blog.full_feature_image ?? null,
    author_image_url:
      blog.user?.full_profile_image ?? imgUrl(blog.user?.profile_image) ?? null,
  }
}

export function previewStatusLabel(status: string): string {
  return (
    {
      [BLOG_STATUS.PUBLISHED]: 'Published',
      [BLOG_STATUS.DRAFT]: 'Draft',
      [BLOG_STATUS.ARCHIVE]: 'Archive',
    }[status] ?? 'Unknown'
  )
}

export function previewStatusClassName(status: string): string {
  return (
    {
      [BLOG_STATUS.PUBLISHED]: 'bg-sky-100 text-sky-800',
      [BLOG_STATUS.DRAFT]: 'bg-amber-100 text-amber-800',
      [BLOG_STATUS.ARCHIVE]: 'bg-gray-100 text-gray-700',
    }[status] ?? 'bg-gray-100 text-gray-700'
  )
}

export function formatPreviewDate(value: string): string {
  if (!value) return '—'
  // datetime-local values (YYYY-MM-DDTHH:mm) parse cleanly via Date
  return formatDate(value.includes('T') ? value : value.replace(' ', 'T'))
}
