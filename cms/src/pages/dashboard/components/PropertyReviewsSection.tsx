import { useEffect, useMemo, useState } from 'react'
import {
  dashboardAPI,
  extractPropertyReviewsChartData,
  type DashboardCompany,
  type DashboardPropertyBar,
  type DashboardState,
  type DashboardSuburb,
} from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  buildPropertyReviewsOptionsPayload,
  DEFAULT_PROPERTY_REVIEWS_FILTERS,
  formatPropertyReviewsExportFilters,
  toSelectOptions,
} from '@/pages/dashboard/shared/dashboardUtils'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'
import {
  PropertyReviewsFilter,
  type PropertyReviewsFilterValues,
} from './PropertyReviewsFilter'
import { PropertyReviewsChart } from './PropertyReviewsChart'

type PropertyReviewsSectionProps = {
  initialData?: DashboardPropertyBar[]
  initialCompanies?: DashboardCompany[]
  initialStates?: DashboardState[]
}

export function PropertyReviewsSection({
  initialData = [],
  initialCompanies = [],
  initialStates = [],
}: PropertyReviewsSectionProps) {
  const [filters, setFilters] = useState<PropertyReviewsFilterValues>({
    ...DEFAULT_PROPERTY_REVIEWS_FILTERS,
  })
  const [chartData, setChartData] = useState<DashboardPropertyBar[]>(initialData)
  const [companyOptions, setCompanyOptions] = useState<SelectDropdownOption[]>(
    () => toSelectOptions(initialCompanies)
  )
  const [stateOptions, setStateOptions] = useState<SelectDropdownOption[]>(() =>
    toSelectOptions(initialStates)
  )
  const [states, setStates] = useState<DashboardState[]>(initialStates)
  const [suburbs, setSuburbs] = useState<DashboardSuburb[]>([])
  const [suburbOptions, setSuburbOptions] = useState<SelectDropdownOption[]>([])
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)

  useEffect(() => {
    if (hasSearched) return

    setChartData(initialData)
    setCompanyOptions(toSelectOptions(initialCompanies))
    setStateOptions(toSelectOptions(initialStates))
    setStates(initialStates)
  }, [initialData, initialCompanies, initialStates, hasSearched])

  const fetchChart = async (
    nextFilters: PropertyReviewsFilterValues,
    lookup: {
      states: DashboardState[]
      suburbs: DashboardSuburb[]
    }
  ) => {
    setLoading(true)
    setHasSearched(true)
    try {
      const res = await dashboardAPI.getPropertyOptions(
        buildPropertyReviewsOptionsPayload(nextFilters, lookup),
        { showErrorToast: false }
      )

      setChartData(extractPropertyReviewsChartData(res))

      if (res.companies?.length) {
        setCompanyOptions(toSelectOptions(res.companies))
      }

      const nextStates = res.states ?? res.options?.state
      if (nextStates?.length) {
        setStates(nextStates)
        setStateOptions(toSelectOptions(nextStates))
      }

      const nextSuburbs = res.suburbs ?? res.options?.suburb
      if (nextSuburbs?.length) {
        setSuburbs(nextSuburbs)
        setSuburbOptions(toSelectOptions(nextSuburbs))
      } else {
        setSuburbs([])
        setSuburbOptions([])
      }
    } catch {
      setChartData([])
    } finally {
      setLoading(false)
    }
  }

  const exportSubtitle = useMemo(
    () =>
      formatPropertyReviewsExportFilters(filters, {
        companyOptions,
        stateOptions,
        suburbOptions,
      }),
    [filters, companyOptions, stateOptions, suburbOptions]
  )

  return (
    <DashboardSectionCard
      title="Property Reviews"
      exportSubtitle={exportSubtitle}
      data-dashboard-export="property-reviews"
    >
      <div className="px-5 pt-5">
        <PropertyReviewsFilter
          values={filters}
          companyOptions={companyOptions}
          stateOptions={stateOptions}
          suburbOptions={suburbOptions}
          loading={loading}
          onChange={setFilters}
          onSearch={() => void fetchChart(filters, { states, suburbs })}
        />
      </div>

      <div className="px-5 py-6">
        <PropertyReviewsChart data={chartData} loading={loading} />
      </div>
    </DashboardSectionCard>
  )
}
