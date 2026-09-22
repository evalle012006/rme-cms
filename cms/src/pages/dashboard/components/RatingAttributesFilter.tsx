import { SelectDropdown } from '@/components/atoms/SelectDropdown'
import { Button } from '@/components/atoms/Button'
import {
  EMPTY_LOCALITY_SELECTION,
  LocalitySelect,
  type LocalitySelection,
} from '@/components/atoms/LocalitySelect'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

export type RatingAttributesFilterValues = {
  companyId: string
  locality: LocalitySelection
  projectId: string
}

type RatingAttributesFilterProps = {
  values: RatingAttributesFilterValues
  companyOptions: SelectDropdownOption[]
  projectOptions: SelectDropdownOption[]
  loading?: boolean
  onChange: (values: RatingAttributesFilterValues) => void
  onSearch: () => void
}

export function RatingAttributesFilter({
  values,
  companyOptions,
  projectOptions,
  loading = false,
  onChange,
  onSearch,
}: RatingAttributesFilterProps) {
  const update = (patch: Partial<RatingAttributesFilterValues>) => {
    onChange({ ...values, ...patch })
  }

  return (
    <div className="flex flex-wrap items-end gap-3">
      <div className="w-44">
        <SelectDropdown
          placeholder="-- Select Company --"
          options={companyOptions}
          value={values.companyId}
          onChange={(event) =>
            update({
              companyId: event.target.value,
              locality: EMPTY_LOCALITY_SELECTION,
              projectId: '',
            })
          }
        />
      </div>

      <LocalitySelect
        className="w-full sm:w-auto"
        value={values.locality}
        anyStateLabel="-- All States --"
        anySuburbLabel="-- All Suburbs --"
        onChange={(locality) => update({ locality, projectId: '' })}
      />

      <div className="w-44">
        <SelectDropdown
          placeholder="-- All Projects --"
          options={projectOptions}
          value={values.projectId}
          onChange={(event) => update({ projectId: event.target.value })}
          disabled={projectOptions.length === 0}
        />
      </div>

      <Button
        color="primary"
        className="h-auto px-6 py-3"
        onClick={onSearch}
        disabled={loading}
      >
        {loading ? 'Searching...' : 'Search'}
      </Button>
    </div>
  )
}
