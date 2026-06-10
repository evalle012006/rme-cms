import { useEffect, useState } from 'react'
import {
  dashboardAPI,
  type DashboardCompany,
  type DashboardPropertyOption,
  type DashboardState,
} from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  buildRatingAttributesPayload,
  toPropertySelectOptions,
  toSelectOptions,
} from '@/pages/dashboard/shared/dashboardUtils'
import {
  RatingAttributesFilter,
  type RatingAttributesFilterValues,
} from './RatingAttributesFilter'
import { RatingAttributesChart } from './RatingAttributesChart'

type RatingAttributesSectionProps = {
  initialData?: Record<string, number | null>
  initialCompanies?: DashboardCompany[]
  initialStates?: DashboardState[]
  initialProjects?: DashboardPropertyOption[]
}

const defaultFilters: RatingAttributesFilterValues = {
  companyId: '',
  stateId: '',
  suburbId: '',
  projectId: '',
}

export function RatingAttributesSection({
  initialData = {},
  initialCompanies = [],
  initialStates = [],
  initialProjects = [],
}: RatingAttributesSectionProps) {
  const [filters, setFilters] =
    useState<RatingAttributesFilterValues>(defaultFilters)
  const [chartData, setChartData] =
    useState<Record<string, number | null>>(initialData)
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)

  useEffect(() => {
    if (!hasSearched && Object.keys(initialData).length) {
      setChartData(initialData)
    }
  }, [initialData, hasSearched])

  const fetchChart = async (nextFilters = filters) => {
    setLoading(true)
    setHasSearched(true)
    try {
      const data = await dashboardAPI.filterRatingAttributesChart(
        buildRatingAttributesPayload(nextFilters),
        { showErrorToast: false }
      )
      setChartData(data)
    } catch {
      setChartData({})
    } finally {
      setLoading(false)
    }
  }

  return (
    <DashboardSectionCard
      title="Rating Attributes"
      headerRight={
        <RatingAttributesFilter
          values={filters}
          companyOptions={toSelectOptions(initialCompanies)}
          stateOptions={toSelectOptions(initialStates)}
          suburbOptions={[]}
          projectOptions={toPropertySelectOptions(initialProjects)}
          loading={loading}
          onChange={setFilters}
          onSearch={() => void fetchChart()}
        />
      }
    >
      <div className="px-5 py-6">
        <RatingAttributesChart data={chartData} loading={loading} />
      </div>
    </DashboardSectionCard>
  )
}
