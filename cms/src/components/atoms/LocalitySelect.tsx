import { useEffect, useMemo, useState } from 'react'
import { FormField } from '@/components/atoms/FormField'
import { ScrollableSelectDropdown } from '@/components/atoms/ScrollableSelectDropdown'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'
import {
  localitiesAPI,
  type Locality,
} from '@/api/localities/localitiesAPIHandler'
import { cn } from '@/lib/utils'
import { useLocalitiesByState, useLocalityStates } from './useLocalities'

/** Matches App\Models\Suburbs::normalise() so both ends agree on a match. */
export function normaliseSuburb(name: string) {
  return (name ?? '')
    .toLowerCase()
    .replace(/['\u2019`]/g, '')
    .replace(/[^a-z0-9]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

export type LocalitySelection = {
  stateCode: string
  /** review_states.id, which is what the reviews table stores. */
  stateId: string
  stateName: string
  suburb: string
  postcode: string
  /**
   * Locality id as a string. Empty while the selection is incomplete — a suburb
   * spanning several postcodes stays unresolved until one is picked.
   */
  localityId: string
}

export type LocalitySelectProps = {
  value: LocalitySelection
  onChange: (selection: LocalitySelection) => void
  layout?: 'row' | 'grid'
  showPostcode?: boolean
  disabled?: boolean
  /** Copy for the empty option. Filters say "All …", forms prompt instead. */
  anyStateLabel?: string
  anySuburbLabel?: string
  errors?: Partial<Record<'state' | 'suburb' | 'postcode', string>>
  className?: string
}

export const EMPTY_LOCALITY_SELECTION: LocalitySelection = {
  stateCode: '',
  stateId: '',
  stateName: '',
  suburb: '',
  postcode: '',
  localityId: '',
}

/**
 * State / Suburb / Postcode selection against the locality reference table.
 *
 * The public review form has its own select2 build of the same behaviour; both
 * read the same /api/localities contract so the two stay consistent.
 */
export function LocalitySelect({
  value,
  onChange,
  layout = 'row',
  showPostcode = true,
  disabled = false,
  anyStateLabel = 'All states',
  anySuburbLabel = 'All suburbs',
  errors,
  className,
}: LocalitySelectProps) {
  const { states, loading: statesLoading } = useLocalityStates()
  const [searchResults, setSearchResults] = useState<Locality[] | null>(null)

  // A legacy review predates the locality FK and only knows its review_states
  // id, so fall back to that to preselect the state.
  const stateCode = useMemo(() => {
    if (value.stateCode) return value.stateCode
    if (!value.stateId) return ''
    return states.find((row) => String(row.id) === value.stateId)?.code ?? ''
  }, [states, value.stateCode, value.stateId])

  const { localities, loading: localitiesLoading } =
    useLocalitiesByState(stateCode)

  const selectedSuburbKey = normaliseSuburb(value.suburb)

  // The suburb dropdown lists names and the postcode field disambiguates.
  // Server search results replace the state list while a query is active.
  const suburbSource = searchResults ?? localities

  const suburbOptions = useMemo<SelectDropdownOption[]>(() => {
    const byName = new Map<string, { suburb: string; postcodes: string[] }>()

    suburbSource.forEach((row) => {
      const key = normaliseSuburb(row.suburb)
      const entry = byName.get(key)
      if (entry) {
        if (!entry.postcodes.includes(row.postcode)) {
          entry.postcodes.push(row.postcode)
        }
        return
      }
      byName.set(key, { suburb: row.suburb, postcodes: [row.postcode] })
    })

    const rows = Array.from(byName.entries())
      .map(([key, entry]) => ({
        value: key,
        label:
          entry.postcodes.length > 1
            ? `${entry.suburb} (${[...entry.postcodes].sort().join(', ')})`
            : entry.suburb,
      }))
      .sort((a, b) => a.label.localeCompare(b.label))

    // A review can reference a legacy or retired locality that is not in the
    // list. Show the stored value so the field is never silently blank.
    if (selectedSuburbKey && !byName.has(selectedSuburbKey)) {
      rows.unshift({
        value: selectedSuburbKey,
        label: value.postcode
          ? `${value.suburb} (${value.postcode})`
          : value.suburb,
      })
    }

    return rows
  }, [selectedSuburbKey, suburbSource, value.postcode, value.suburb])

  /** Localities in the current state matching a suburb name, postcode order. */
  const matchesForSuburb = (suburbKey: string) =>
    localities
      .filter((row) => normaliseSuburb(row.suburb) === suburbKey)
      .sort((a, b) => a.postcode.localeCompare(b.postcode))

  const postcodeMatches = selectedSuburbKey
    ? matchesForSuburb(selectedSuburbKey)
    : []

  const stateFor = (stateCode: string) =>
    states.find((row) => row.code === stateCode)

  const emitLocality = (locality: Locality) => {
    const state = stateFor(locality.state_code)
    onChange({
      stateCode: locality.state_code,
      stateId: state ? String(state.id) : '',
      stateName: state?.name ?? locality.state_name,
      localityId: String(locality.id),
      suburb: locality.suburb,
      postcode: locality.postcode,
    })
  }

  /** Keep the state, drop everything below it. */
  const emitStateOnly = (stateCode: string, suburb = '') => {
    const state = stateFor(stateCode)
    onChange({
      ...EMPTY_LOCALITY_SELECTION,
      stateCode,
      stateId: state ? String(state.id) : '',
      stateName: state?.name ?? '',
      suburb,
    })
  }

  const handleStateChange = (stateId: string) => {
    setSearchResults(null)
    const state = states.find((row) => String(row.id) === stateId)
    // Changing state clears the suburb and postcode beneath it.
    emitStateOnly(state?.code ?? '')
  }

  const handleSuburbChange = (suburbKey: string) => {
    if (!suburbKey) {
      emitStateOnly(stateCode)
      return
    }

    // A search result can belong to another state; adopt that state with it.
    const searchHit = searchResults?.find(
      (row) => normaliseSuburb(row.suburb) === suburbKey
    )
    if (searchHit && searchHit.state_code !== stateCode) {
      setSearchResults(null)
      emitLocality(searchHit)
      return
    }

    const matches = matchesForSuburb(suburbKey)
    const [onlyMatch] = matches
    if (onlyMatch && matches.length === 1) {
      emitLocality(onlyMatch)
      return
    }

    // Several postcodes: hold the suburb but stay unresolved until one is
    // picked, so an ambiguous location can never be submitted.
    emitStateOnly(stateCode, matches[0]?.suburb ?? '')
  }

  const handlePostcodeChange = (postcode: string) => {
    const match = postcodeMatches.find((row) => row.postcode === postcode)
    if (match) {
      emitLocality(match)
      return
    }
    emitStateOnly(stateCode, value.suburb)
  }

  const handleSuburbSearch = (query: string) => {
    const trimmed = query.trim()
    if (trimmed.length < 2) {
      setSearchResults(null)
      return
    }

    localitiesAPI
      .search(
        { q: trimmed, stateCode: stateCode || undefined },
        { showErrorToast: false }
      )
      .then((rows) => setSearchResults(rows ?? []))
      .catch(() => setSearchResults([]))
  }

  // Drop stale results when the state changes underneath an open dropdown.
  useEffect(() => {
    setSearchResults(null)
  }, [stateCode])

  const stateOptions = useMemo<SelectDropdownOption[]>(
    () =>
      states.map((state) => ({
        value: String(state.id),
        label: `${state.name} (${state.code})`,
      })),
    [states]
  )

  const selectedStateId = useMemo(() => {
    const state = stateFor(stateCode)
    return state ? String(state.id) : ''
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [states, stateCode])

  const postcodeOptions = useMemo<SelectDropdownOption[]>(
    () =>
      postcodeMatches.map((row) => ({
        value: row.postcode,
        label: row.postcode,
      })),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [selectedSuburbKey, localities]
  )

  const hasState = stateCode.trim().length > 0
  const wrapperClass =
    layout === 'grid'
      ? 'grid grid-cols-1 gap-4 sm:grid-cols-3'
      : 'flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4'
  const fieldClass = layout === 'grid' ? '' : 'w-full sm:flex-1'

  return (
    <div className={cn(wrapperClass, className)}>
      <FormField label="State" error={errors?.state} className={fieldClass}>
        <ScrollableSelectDropdown
          options={stateOptions}
          value={selectedStateId}
          placeholder={anyStateLabel}
          disabled={disabled}
          loading={statesLoading}
          onChange={handleStateChange}
        />
      </FormField>

      <FormField label="Suburb" error={errors?.suburb} className={fieldClass}>
        <ScrollableSelectDropdown
          searchable
          searchPlaceholder="Search suburb…"
          options={suburbOptions}
          value={selectedSuburbKey}
          placeholder={hasState ? anySuburbLabel : 'Select state first'}
          disabled={disabled || !hasState}
          loading={localitiesLoading}
          onChange={handleSuburbChange}
          onSearchChange={handleSuburbSearch}
          emptyMessage="No suburbs found"
        />
      </FormField>

      {showPostcode && (
        <FormField
          label="Postcode"
          error={errors?.postcode}
          className={fieldClass}
        >
          {postcodeMatches.length > 1 ? (
            // A suburb spanning several postcodes offers them all, no default.
            <ScrollableSelectDropdown
              options={postcodeOptions}
              value={value.postcode}
              placeholder="Select Postcode"
              disabled={disabled}
              onChange={handlePostcodeChange}
            />
          ) : (
            <input
              type="text"
              readOnly
              value={value.postcode}
              placeholder="Postcode"
              aria-label="Postcode"
              className="w-full rounded-card border border-gray-300 bg-gray-50 py-2.5 pr-3 pl-3.5 text-sm text-gray-700"
            />
          )}
        </FormField>
      )}
    </div>
  )
}
