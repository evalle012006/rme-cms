import { z } from 'zod'
import { PROPERTY_STATUS, type Property } from '@/api/properties/propertiesAPIHandler'
import type { State } from '@/api/states/statesAPIHandler'
import { ROUTES } from '@/constants/routes'

export const DEFAULT_LAT = '-33.8688'
export const DEFAULT_LNG = '151.2093'

export const STATUS_OPTIONS = [
  { value: String(PROPERTY_STATUS.PENDING), label: 'Pending' },
  { value: String(PROPERTY_STATUS.APPROVED), label: 'Approved' },
] as const

export const propertySchema = z.object({
  project_name: z.string().min(1, 'Project name is required'),
  developer_name: z.string().min(1, 'Company is required'),
  status: z.string().min(1, 'Status is required'),
  phone: z.string().min(1, 'Phone is required'),
  website_url: z.string().min(1, 'Website is required'),
  description: z.string().min(1, 'Description is required'),
  property_categories: z
    .array(z.number())
    .min(1, 'Select at least one category'),
  address: z.string().min(1, 'Address is required'),
  country: z.string().min(1, 'Country is required'),
  state: z.string().min(1, 'State is required'),
  city: z.string().min(1, 'City is required'),
  post_code: z.string().min(1, 'Post code is required'),
  suburb: z.string().min(1, 'Suburb is required'),
  lat: z.string(),
  long: z.string(),
})

export type PropertyFormData = z.infer<typeof propertySchema>

export function resolveReturnPath(from: string | null, status?: string) {
  if (from === 'approved') return ROUTES.properties.approved
  if (from === 'pending') return ROUTES.properties.pending
  return status === String(PROPERTY_STATUS.APPROVED)
    ? ROUTES.properties.approved
    : ROUTES.properties.pending
}

export function resolveStateName(
  stateValue: string,
  states: State[]
): string {
  const byId = states.find((s) => String(s.id) === stateValue)
  if (byId) return byId.state_name
  return stateValue
}

export function resolveStateId(
  stateValue: string | null | undefined,
  states: State[]
): string {
  if (!stateValue) return ''
  const byId = states.find((s) => String(s.id) === stateValue)
  if (byId) return String(byId.id)
  const byName = states.find(
    (s) => s.state_name.toLowerCase() === stateValue.toLowerCase()
  )
  return byName ? String(byName.id) : ''
}

export function toFormValues(
  property: Property,
  states: State[]
): PropertyFormData {
  return {
    project_name: property.project_name ?? '',
    developer_name: property.developer_name
      ? String(property.developer_name)
      : '',
    status: String(property.status),
    phone: property.phone ?? '',
    website_url: property.website_url ?? '',
    description: property.description ?? '',
    property_categories: (property.property_allowed_category ?? []).map(
      (cat) => cat.id
    ),
    address: property.address ?? '',
    country: property.country ?? '',
    state: resolveStateId(property.state, states),
    city: property.city ?? '',
    post_code: property.post_code ?? '',
    suburb: property.suburb ?? '',
    lat:
      property.lat != null && property.lat !== ''
        ? String(property.lat)
        : DEFAULT_LAT,
    long:
      property.long != null && property.long !== ''
        ? String(property.long)
        : DEFAULT_LNG,
  }
}

export function toPropertySubmitPayload(
  data: PropertyFormData,
  states: State[]
) {
  return {
    project_name: data.project_name,
    developer_name: Number(data.developer_name),
    status: Number(data.status),
    phone: data.phone,
    website_url: data.website_url,
    description: data.description,
    property_categories: data.property_categories,
    address: data.address,
    country: data.country,
    state: resolveStateName(data.state, states),
    city: data.city,
    post_code: data.post_code,
    suburb: data.suburb,
    lat: data.lat,
    long: data.long,
  }
}

export function SectionHeader({ title }: { title: string }) {
  return (
    <div className="border-b border-gray-200 bg-gray-50 px-6 py-3">
      <h2 className="text-sm font-medium text-gray-700">{title}</h2>
    </div>
  )
}
