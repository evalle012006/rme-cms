import { SelectDropdown } from '@/components/atoms/SelectDropdown'
import { Button } from '@/components/atoms/Button'
import {
  EMPTY_LOCALITY_SELECTION,
  LocalitySelect,
  type LocalitySelection,
} from '@/components/atoms/LocalitySelect'
import { PROPERTY_REVIEWS_MODE_OPTIONS } from '@/pages/dashboard/shared/dashboardUtils'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

export type PropertyReviewsFilterValues = {
  companyId: string
  mode: string
  locality: LocalitySelection
}

type PropertyReviewsFilterProps = {
  values: PropertyReviewsFilterValues
  companyOptions: SelectDropdownOption[]
  loading?: boolean
  onChange: (values: PropertyReviewsFilterValues) => void
  onSearch: () => void
}

export function PropertyReviewsFilter({
  values,
  companyOptions,
  loading = false,
  onChange,
  onSearch,
}: PropertyReviewsFilterProps) {
  const update = (patch: Partial<PropertyReviewsFilterValues>) => {
    onChange({ ...values, ...patch })
  }

  return (
    <div className="flex flex-wrap items-end gap-3">
      <div className="w-44">
        <SelectDropdown
          placeholder="-- All Companies --"
          options={companyOptions}
          value={values.companyId}
          onChange={(event) =>
            update({
              companyId: event.target.value,
              locality: EMPTY_LOCALITY_SELECTION,
            })
          }
        />
      </div>

      <div className="w-36">
        <SelectDropdown
          placeholder="Top 30"
          options={[...PROPERTY_REVIEWS_MODE_OPTIONS]}
          value={values.mode}
          onChange={(event) => update({ mode: event.target.value })}
        />
      </div>

      <div className="rounded-card border border-gray-200 bg-gray-50 p-2">
        <div className="flex flex-wrap items-end gap-3">
          <LocalitySelect
            className="w-full sm:w-auto"
            value={values.locality}
            anyStateLabel="-- All States --"
            anySuburbLabel="-- All Suburbs --"
            onChange={(locality) => update({ locality })}
          />

          <Button
            color="primary"
            className="h-auto px-6 py-3"
            onClick={onSearch}
            disabled={loading}
          >
            {loading ? 'Searching...' : 'Search'}
          </Button>
        </div>
      </div>
    </div>
  )
}
