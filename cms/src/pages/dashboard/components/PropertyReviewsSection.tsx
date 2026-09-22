import { useEffect, useMemo, useState } from 'react'
import {
  dashboardAPI,
  extractPropertyReviewsChartData,
  type DashboardCompany,
  type DashboardPropertyBar,
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
}

export function PropertyReviewsSection({
  initialData = [],
  initialCompanies = [],
}: PropertyReviewsSectionProps) {
  const [filters, setFilters] = useState<PropertyReviewsFilterValues>({
    ...DEFAULT_PROPERTY_REVIEWS_FILTERS,
  })
  const [chartData, setChartData] = useState<DashboardPropertyBar[]>(initialData)
  const [companyOptions, setCompanyOptions] = useState<SelectDropdownOption[]>(
    () => toSelectOptions(initialCompanies)
  )
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)

  useEffect(() => {
    if (hasSearched) return

    setChartData(initialData)
    setCompanyOptions(toSelectOptions(initialCompanies))
  }, [initialData, initialCompanies, hasSearched])

  const fetchChart = async (nextFilters: PropertyReviewsFilterValues) => {
    setLoading(true)
    setHasSearched(true)
    try {
      const res = await dashboardAPI.getPropertyOptions(
        buildPropertyReviewsOptionsPayload(nextFilters),
        { showErrorToast: false }
      )

      setChartData(extractPropertyReviewsChartData(res))

      if (res.companies?.length) {
        setCompanyOptions(toSelectOptions(res.companies))
      }
    } catch {
      setChartData([])
    } finally {
      setLoading(false)
    }
  }

  const exportSubtitle = useMemo(
    () => formatPropertyReviewsExportFilters(filters, { companyOptions }),
    [filters, companyOptions]
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
          loading={loading}
          onChange={setFilters}
          onSearch={() => void fetchChart(filters)}
        />
      </div>

      <div className="px-5 py-6">
        <PropertyReviewsChart data={chartData} loading={loading} />
      </div>
    </DashboardSectionCard>
  )
}
