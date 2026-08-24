import { SelectDropdown } from '@/components/atoms/SelectDropdown'
import { ScrollableSelectDropdown } from '@/components/atoms/ScrollableSelectDropdown'
import { Button } from '@/components/atoms/Button'
import { PROPERTY_REVIEWS_MODE_OPTIONS } from '@/pages/dashboard/shared/dashboardUtils'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

export type PropertyReviewsFilterValues = {
  companyId: string
  mode: string
  stateId: string
  suburbId: string
}

type PropertyReviewsFilterProps = {
  values: PropertyReviewsFilterValues
  companyOptions: SelectDropdownOption[]
  stateOptions: SelectDropdownOption[]
  suburbOptions: SelectDropdownOption[]
  loading?: boolean
  suburbsLoading?: boolean
  onChange: (values: PropertyReviewsFilterValues) => void
  onSearch: () => void
}

export function PropertyReviewsFilter({
  values,
  companyOptions,
  stateOptions,
  suburbOptions,
  loading = false,
  suburbsLoading = false,
  onChange,
  onSearch,
}: PropertyReviewsFilterProps) {
  const update = (patch: Partial<PropertyReviewsFilterValues>) => {
    onChange({ ...values, ...patch })
  }

  return (
    <div className="flex flex-wrap items-center gap-3">
      <div className="w-44">
        <SelectDropdown
          placeholder="-- All Companies --"
          options={companyOptions}
          value={values.companyId}
          onChange={(event) =>
            update({
              companyId: event.target.value,
              suburbId: '',
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
        <div className="flex flex-wrap items-center gap-3">
          <div className="w-44">
            <SelectDropdown
              placeholder="-- All States --"
              options={stateOptions}
              value={values.stateId}
              onChange={(event) =>
                update({
                  stateId: event.target.value,
                  suburbId: '',
                })
              }
              className="border-primary-400 bg-primary-50"
            />
          </div>

          <div className="w-44">
            <ScrollableSelectDropdown
              placeholder={
                suburbsLoading ? 'Loading suburbs...' : '-- All Suburbs --'
              }
              options={suburbsLoading ? [] : suburbOptions}
              value={suburbsLoading ? '' : values.suburbId}
              onChange={(suburbId) => update({ suburbId })}
              className="border-primary-400 bg-primary-50"
              disabled={!values.stateId || suburbsLoading}
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
      </div>
    </div>
  )
}
