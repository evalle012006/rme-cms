import {
  EMPTY_LOCALITY_SELECTION,
  type LocalitySelection,
} from '@/components/atoms/LocalitySelect'

export const STAR_LABELS_DESC = ['5 star', '4 star', '3 star', '2 star', '1 star']
export const STAR_LABELS_ASC = ['1 star', '2 star', '3 star', '4 star', '5 star']
export const RATING_ATTR_X_LABELS = [
  '0 star',
  '1 star',
  '2 star',
  '3 star',
  '4 star',
  '5 star',
]

export const PROPERTY_REVIEWS_MODE_OPTIONS = [
  { value: 'first', label: 'Top 30' },
  { value: 'last', label: 'Bottom 30' },
] as const

export type DateRange = {
  from: string
  to: string
}

export function formatApiDate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function parseApiDate(value: string): Date {
  const [yearRaw, monthRaw, dayRaw] = value.split('-')
  const year = Number(yearRaw) || new Date().getFullYear()
  const month = Number(monthRaw) || 1
  const day = Number(dayRaw) || 1
  return new Date(year, month - 1, day)
}

export function getCurrentWeekRange(): DateRange {
  const now = new Date()
  const day = now.getDay()
  const mondayOffset = day === 0 ? -6 : 1 - day

  const fromDate = new Date(now)
  fromDate.setDate(now.getDate() + mondayOffset)

  const toDate = new Date(fromDate)
  toDate.setDate(fromDate.getDate() + 6)

  return {
    from: formatApiDate(fromDate),
    to: formatApiDate(toDate),
  }
}

export function getCurrentMonthRange(): DateRange {
  const now = new Date()
  const from = new Date(now.getFullYear(), now.getMonth(), 1)
  const to = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  return {
    from: formatApiDate(from),
    to: formatApiDate(to),
  }
}

export function toReviewCountChartPayload(range: DateRange) {
  return {
    start: `${range.from} 00:00:00`,
    end: `${range.to} 23:59:59`,
  }
}

export function formatDisplayDateRange(from: string, to: string): string {
  const formatPart = (value: string) => {
    const [year, month, day] = value.split('-')
    if (!year || !month || !day) return value
    return `${month}-${day}-${year}`
  }

  return `${formatPart(from)} - ${formatPart(to)}`
}

export function formatChartDayLabel(date: Date): string {
  const day = String(date.getDate()).padStart(2, '0')
  const month = date.toLocaleString('en-AU', { month: 'short' })
  return `${day} ${month}`
}

export function getChartLabelsInRange(from: string, to: string): string[] {
  const start = parseApiDate(from)
  const end = parseApiDate(to)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return []

  const labels: string[] = []
  const cursor = new Date(start)

  while (cursor <= end) {
    labels.push(formatChartDayLabel(cursor))
    cursor.setDate(cursor.getDate() + 1)
  }

  return labels
}

export function isDateWithinRange(
  date: Date,
  range: DateRange | null
): boolean {
  if (!range) return false

  const value = formatApiDate(date)
  return value >= range.from && value <= range.to
}

export function isSameApiDate(a: Date, b: Date): boolean {
  return formatApiDate(a) === formatApiDate(b)
}

export const MAX_REVIEW_COUNT_RANGE_DAYS = 30

export function addDaysToDate(date: Date, days: number): Date {
  const result = new Date(date)
  result.setDate(result.getDate() + days)
  return result
}

export function isSelectingRangeEnd(range: DateRange): boolean {
  return range.from === range.to
}

export function isDateSelectableForRangeEnd(
  date: Date,
  anchor: Date,
  maxDays = MAX_REVIEW_COUNT_RANGE_DAYS
): boolean {
  const value = formatApiDate(date)
  const anchorValue = formatApiDate(anchor)
  const maxValue = formatApiDate(addDaysToDate(anchor, maxDays))
  return value >= anchorValue && value <= maxValue
}

export function clampDateRangeToMaxDays(
  range: DateRange,
  maxDays = MAX_REVIEW_COUNT_RANGE_DAYS
): DateRange {
  const start = parseApiDate(range.from)
  const end = parseApiDate(range.to)
  const maxEnd = addDaysToDate(start, maxDays)

  if (end <= maxEnd) return range

  return {
    from: range.from,
    to: formatApiDate(maxEnd),
  }
}

export function toSelectOptions<T extends { id: number; name: string }>(
  items: T[] | undefined
) {
  return (items ?? []).map((item) => ({
    value: String(item.id),
    label: item.name,
  }))
}

export function toPropertySelectOptions(
  items: Array<{ id: number; name?: string; project_name?: string }> | undefined
) {
  return (items ?? []).map((item) => ({
    value: String(item.id),
    label: item.project_name ?? item.name ?? `Project ${item.id}`,
  }))
}

export const DEFAULT_PROPERTY_REVIEWS_FILTERS = {
  companyId: '',
  mode: 'first',
  locality: EMPTY_LOCALITY_SELECTION,
} as const

export function buildPropertyReviewsOptionsPayload(filters: {
  companyId: string
  mode: string
  locality: LocalitySelection
}) {
  const payload: {
    company_id?: number | null
    mode: 'first' | 'last'
    state: string | null
    suburb: string | null
  } = {
    mode: filters.mode === 'last' ? 'last' : 'first',
    // This endpoint filters properties, which have no locality FK, so the names
    // are taken off the selected locality row.
    state: filters.locality.stateName || null,
    suburb: filters.locality.suburb || null,
  }

  if (filters.companyId) {
    payload.company_id = Number(filters.companyId)
  } else {
    payload.company_id = null
  }

  return payload
}

export function buildRatingAttributesPayload(filters: {
  companyId: string
  projectId: string
  locality: LocalitySelection
}) {
  return {
    company_id: filters.companyId ? Number(filters.companyId) : null,
    project_id: filters.projectId ? Number(filters.projectId) : null,
    // Reviews carry a locality FK, so both location filters are exact.
    state_code: filters.locality.stateCode || null,
    locality_id: filters.locality.localityId
      ? Number(filters.locality.localityId)
      : null,
  }
}

export function getMaxChartValue(values: number[]): number {
  const max = Math.max(...values, 0)
  if (max <= 5) return 5
  return Math.ceil(max / 5) * 5
}

type ExportFilterOption = {
  value: string
  label: string
}

function getExportFilterOptionLabel(
  value: string,
  options: ExportFilterOption[]
): string | null {
  if (!value) {
    return null
  }

  return options.find((option) => option.value === value)?.label ?? null
}

const EXPORT_FILTER_SEPARATOR = '   |   '

function formatExportFilterSummary(
  parts: Array<{ label: string; value: string | null }>
): string | undefined {
  const applied = parts
    .filter((part) => part.value)
    .map((part) => `${part.label}: ${part.value}`)

  return applied.length > 0 ? applied.join(EXPORT_FILTER_SEPARATOR) : undefined
}

/** State and suburb read straight off the selected locality. */
function localityExportParts(locality: LocalitySelection) {
  return [
    { label: 'State', value: locality.stateName || null },
    {
      label: 'Suburb',
      value: locality.suburb
        ? locality.postcode
          ? `${locality.suburb} ${locality.postcode}`
          : locality.suburb
        : null,
    },
  ]
}

export function formatPropertyReviewsExportFilters(
  filters: {
    companyId: string
    mode: string
    locality: LocalitySelection
  },
  options: {
    companyOptions: ExportFilterOption[]
  }
): string | undefined {
  const modeLabel = PROPERTY_REVIEWS_MODE_OPTIONS.find(
    (option) => option.value === filters.mode
  )?.label

  return formatExportFilterSummary([
    {
      label: 'Company',
      value: getExportFilterOptionLabel(filters.companyId, options.companyOptions),
    },
    { label: 'View', value: modeLabel ?? null },
    ...localityExportParts(filters.locality),
  ])
}

export function formatRatingAttributesExportFilters(
  filters: {
    companyId: string
    locality: LocalitySelection
    projectId: string
  },
  options: {
    companyOptions: ExportFilterOption[]
    projectOptions: ExportFilterOption[]
  }
): string | undefined {
  return formatExportFilterSummary([
    {
      label: 'Company',
      value: getExportFilterOptionLabel(filters.companyId, options.companyOptions),
    },
    ...localityExportParts(filters.locality),
    {
      label: 'Project',
      value: getExportFilterOptionLabel(filters.projectId, options.projectOptions),
    },
  ])
}
