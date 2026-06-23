import {
  apiClient,
  apiRequest,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { imgUrl } from '@/lib/utils'
import { CMS_ENDPOINTS } from './cmsAPIEndpoints'

export interface CmsPageSection {
  id: number
  section: string
  section_slug?: string
  section_name?: string
  title: string | null
  image: string | null
  description: string | null
  trust_online_ratio?: string | null
  validate_reviews_ratio?: string | null
  other_params: string | Record<string, string> | null
  full_image?: string | null
}

export type CmsSavePayload = Record<string, string | number | File>

function appendFormValue(fd: FormData, key: string, value: string | number | File) {
  fd.append(key, value instanceof File ? value : String(value))
}

function isRawCmsSection(value: unknown): value is Record<string, unknown> {
  return (
    typeof value === 'object' &&
    value !== null &&
    'id' in value &&
    ('section' in value || 'section_slug' in value || 'section_name' in value)
  )
}

function readNullableString(value: unknown): string | null {
  if (typeof value === 'string') return value
  return value === null ? null : null
}

function normalizeCmsSection(raw: Record<string, unknown>): CmsPageSection {
  const section =
    readNullableString(raw.section) ??
    readNullableString(raw.section_name) ??
    readNullableString(raw.section_slug) ??
    ''

  return {
    id: Number(raw.id),
    section,
    section_slug: readNullableString(raw.section_slug) ?? undefined,
    section_name: readNullableString(raw.section_name) ?? undefined,
    title: readNullableString(raw.title),
    image: readNullableString(raw.image),
    description:
      readNullableString(raw.body) ?? readNullableString(raw.description),
    trust_online_ratio: readNullableString(raw.trust_online_ratio) ?? undefined,
    validate_reviews_ratio:
      readNullableString(raw.validate_reviews_ratio) ?? undefined,
    other_params:
      (raw.other_params as CmsPageSection['other_params']) ?? null,
    full_image: readNullableString(raw.full_image) ?? undefined,
  }
}

export function normalizeCmsSections(data: unknown): CmsPageSection[] {
  if (Array.isArray(data)) {
    return data.filter(isRawCmsSection).map(normalizeCmsSection)
  }

  if (data && typeof data === 'object') {
    const record = data as Record<string, unknown>

    if (Array.isArray(record.sections)) {
      return record.sections.filter(isRawCmsSection).map(normalizeCmsSection)
    }

    if (Array.isArray(record.data)) {
      return record.data.filter(isRawCmsSection).map(normalizeCmsSection)
    }

    return Object.values(record).filter(isRawCmsSection).map(normalizeCmsSection)
  }

  return []
}

export const cmsAPI = {
  getPage: (page: string, options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(CMS_ENDPOINTS.GET, { page }),
      options
    ),

  saveSection: async (
    payload: CmsSavePayload,
    options?: ApiRequestOptions
  ): Promise<unknown> => {
    const resolvedEntries = await Promise.all(
      Object.entries(payload).map(async ([key, value]) => {
        if (value === undefined || value === null) return null

        const resolvedValue =
          value instanceof File ? await uploadAPI.uploadImage(value) : value

        return [key, resolvedValue] as const
      })
    )

    const fd = new FormData()
    resolvedEntries.forEach((entry) => {
      if (entry) {
        appendFormValue(fd, entry[0], entry[1])
      }
    })

    return apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(CMS_ENDPOINTS.SAVE, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      options
    )
  },
}

export function parseCmsOtherParams(
  otherParams: CmsPageSection['other_params']
): Record<string, string> {
  if (!otherParams) return {}
  if (typeof otherParams === 'string') {
    try {
      return JSON.parse(otherParams) as Record<string, string>
    } catch {
      return {}
    }
  }
  return otherParams
}

export function getCmsImageUrl(section: CmsPageSection): string | null {
  if (section.full_image) return section.full_image
  return imgUrl(section.image)
}
