import { useEffect, useMemo, useState } from 'react'
import {
  dashboardAPI,
  extractPropertyReviewsChartData,
  type DashboardCompany,
  type DashboardPropertyBar,
  type DashboardState,
} from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  buildPropertyReviewsOptionsPayload,
  DEFAULT_PROPERTY_REVIEWS_FILTERS,
  formatPropertyReviewsExportFilters,
  toSelectOptions,
} from '@/pages/dashboard/shared/dashboardUtils'
import { useSuburbsByState } from '@/pages/dashboard/shared/useSuburbsByState'
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
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)
  const { suburbOptions, loading: suburbsLoading } = useSuburbsByState(
    filters.stateId
  )

  useEffect(() => {
    if (hasSearched) return

    setChartData(initialData)
    setCompanyOptions(toSelectOptions(initialCompanies))
    setStateOptions(toSelectOptions(initialStates))
    setStates(initialStates)
  }, [initialData, initialCompanies, initialStates, hasSearched])

  const suburbLookup = useMemo(
    () =>
      suburbOptions.map((option) => ({
        id: option.value,
        name: option.label,
      })),
    [suburbOptions]
  )

  const fetchChart = async (
    nextFilters: PropertyReviewsFilterValues,
    lookup: {
      states: DashboardState[]
      suburbs: Array<{ id: string; name: string }>
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
      title="Project Reviews"
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
          suburbsLoading={suburbsLoading}
          onChange={setFilters}
          onSearch={() =>
            void fetchChart(filters, { states, suburbs: suburbLookup })
          }
        />
      </div>

      <div className="px-5 py-6">
        <PropertyReviewsChart data={chartData} loading={loading} />
      </div>
    </DashboardSectionCard>
  )
}
