import { z } from 'zod'

export const BLOG_STATUS_OPTIONS = [
  { value: '0', label: 'Publish' },
  { value: '1', label: 'Draft' },
  { value: '2', label: 'Archive' },
] as const

export const blogFormSchema = z.object({
  title: z.string().min(1, 'Title is required'),
  blog_date: z.string().min(1, 'Date is required'),
  author_name: z.string(),
  status: z.string().min(1, 'Status is required'),
  seo_title: z.string(),
  seo_description: z.string(),
  seo_keywords: z.string(),
  category_id: z.string().min(1, 'Blog category is required'),
})

export type BlogFormData = z.infer<typeof blogFormSchema>

export function toDatetimeLocalValue(apiDate: string): string {
  return apiDate.replace(' ', 'T').slice(0, 16)
}

export function formatBlogDateForApi(datetimeLocal: string): string {
  const normalized =
    datetimeLocal.length === 16 ? `${datetimeLocal}:00` : datetimeLocal.slice(0, 19)
  return `${normalized}+05:30`
}

export function nowDatetimeLocal(): string {
  const now = new Date()
  const pad = (value: number) => String(value).padStart(2, '0')
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`
}

export function isDescriptionEmpty(html: string): boolean {
  const text = html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
  return text.length === 0
}
