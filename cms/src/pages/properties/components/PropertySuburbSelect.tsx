import { useEffect, useMemo } from 'react'
import { FormField } from '@/components/atoms/FormField'
import { ScrollableSelectDropdown } from '@/components/atoms/ScrollableSelectDropdown'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'
import type { SuburbByStateWithPostcodeOption } from '@/api/suburbs/suburbsAPIHandler'
import { usePropertySuburbs } from './usePropertySuburbs'

export function suburbOptionKey(name: string, postcode: string) {
  return `${name}|${postcode}`
}

export function parseSuburbOptionKey(key: string): {
  name: string
  postcode: string
} {
  const separator = key.lastIndexOf('|')
  if (separator === -1) return { name: key, postcode: '' }
  return {
    name: key.slice(0, separator),
    postcode: key.slice(separator + 1),
  }
}

function resolveSelectedKey(
  suburbs: SuburbByStateWithPostcodeOption[],
  suburbName: string,
  postCode: string
) {
  if (!suburbName) return ''

  const exact = suburbs.find(
    (suburb) => suburb.name === suburbName && suburb.postcode === postCode
  )
  if (exact) return suburbOptionKey(exact.name, exact.postcode)

  const byName = suburbs.find((suburb) => suburb.name === suburbName)
  if (byName) return suburbOptionKey(byName.name, byName.postcode)

  return suburbOptionKey(suburbName, postCode)
}

type PropertySuburbSelectProps = {
  stateId: string
  value: string
  postCode: string
  onChange: (suburbName: string, postcode: string) => void
  disabled?: boolean
  error?: string
  label?: string
}

export function PropertySuburbSelect({
  stateId,
  value,
  postCode,
  onChange,
  disabled = false,
  error,
  label = 'Suburbs',
}: PropertySuburbSelectProps) {
  const { suburbs, loading } = usePropertySuburbs(stateId)
  const hasState = stateId.trim().length > 0

  const nameCounts = useMemo(() => {
    const counts = new Map<string, number>()
    suburbs.forEach((suburb) => {
      counts.set(suburb.name, (counts.get(suburb.name) ?? 0) + 1)
    })
    return counts
  }, [suburbs])

  const selectedKey = resolveSelectedKey(suburbs, value, postCode)
  const showCurrentValue =
    !!value &&
    !suburbs.some(
      (suburb) => suburbOptionKey(suburb.name, suburb.postcode) === selectedKey
    )

  const options = useMemo<SelectDropdownOption[]>(() => {
    const rows: SelectDropdownOption[] = suburbs.map((suburb) => {
      const key = suburbOptionKey(suburb.name, suburb.postcode)
      return {
        value: key,
        label:
          (nameCounts.get(suburb.name) ?? 0) > 1
            ? `${suburb.name} (${suburb.postcode})`
            : suburb.name,
      }
    })

    if (showCurrentValue) {
      rows.unshift({
        value: selectedKey,
        label: postCode ? `${value} (${postCode})` : value,
      })
    }

    return rows
  }, [nameCounts, postCode, selectedKey, showCurrentValue, suburbs, value])

  useEffect(() => {
    if (!value || loading || suburbs.length === 0) return

    const exact = suburbs.find(
      (suburb) => suburb.name === value && suburb.postcode === postCode
    )
    if (exact) return

    const matches = suburbs.filter((suburb) => suburb.name === value)
    const [uniqueMatch, ...otherMatches] = matches
    if (
      uniqueMatch &&
      otherMatches.length === 0 &&
      uniqueMatch.postcode !== postCode
    ) {
      onChange(uniqueMatch.name, uniqueMatch.postcode)
    }
  }, [value, postCode, suburbs, loading, onChange])

  const placeholder = !hasState
    ? 'Select state first'
    : loading
      ? 'Loading suburbs…'
      : 'Select Suburb'

  return (
    <FormField label={label} error={error}>
      <ScrollableSelectDropdown
        searchable
        searchPlaceholder="Search suburb…"
        options={options}
        value={selectedKey}
        placeholder={placeholder}
        disabled={disabled || !hasState}
        loading={loading}
        onChange={(next) => {
          if (!next) {
            onChange('', '')
            return
          }
          const parsed = parseSuburbOptionKey(next)
          onChange(parsed.name, parsed.postcode)
        }}
      />
    </FormField>
  )
}
