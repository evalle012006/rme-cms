import { SelectDropdown } from '@/components/atoms/SelectDropdown'
import { Button } from '@/components/atoms/Button'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

export type RatingAttributesFilterValues = {
  companyId: string
  stateId: string
  suburbId: string
  projectId: string
}

type RatingAttributesFilterProps = {
  values: RatingAttributesFilterValues
  companyOptions: SelectDropdownOption[]
  stateOptions: SelectDropdownOption[]
  suburbOptions: SelectDropdownOption[]
  projectOptions: SelectDropdownOption[]
  loading?: boolean
  onChange: (values: RatingAttributesFilterValues) => void
  onSearch: () => void
}

export function RatingAttributesFilter({
  values,
  companyOptions,
  stateOptions,
  suburbOptions,
  projectOptions,
  loading = false,
  onChange,
  onSearch,
}: RatingAttributesFilterProps) {
  const update = (patch: Partial<RatingAttributesFilterValues>) => {
    onChange({ ...values, ...patch })
  }

  return (
    <div className="flex flex-wrap items-center gap-3">
      <div className="w-44">
        <SelectDropdown
          placeholder="-- Select Company --"
          options={companyOptions}
          value={values.companyId}
          onChange={(event) =>
            update({
              companyId: event.target.value,
              suburbId: '',
              projectId: '',
            })
          }
        />
      </div>

      <div className="w-44">
        <SelectDropdown
          placeholder="-- All States --"
          options={stateOptions}
          value={values.stateId}
          onChange={(event) =>
            update({
              stateId: event.target.value,
              suburbId: '',
              projectId: '',
            })
          }
        />
      </div>

      <div className="w-44">
        <SelectDropdown
          placeholder="-- All Suburbs --"
          options={suburbOptions}
          value={values.suburbId}
          onChange={(event) =>
            update({
              suburbId: event.target.value,
              projectId: '',
            })
          }
          disabled={!values.stateId}
        />
      </div>

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
