import { useEffect, useMemo, useState } from 'react'
import {
  dashboardAPI,
  type DashboardCompany,
  type DashboardPropertyOption,
  type DashboardState,
} from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  buildRatingAttributesPayload,
  formatRatingAttributesExportFilters,
  toPropertySelectOptions,
  toSelectOptions,
} from '@/pages/dashboard/shared/dashboardUtils'
import { useSuburbsByState } from '@/pages/dashboard/shared/useSuburbsByState'
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

function normalizeChartData(
  data: Record<string, number | null> | unknown
): Record<string, number | null> {
  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    return {}
  }
  return data as Record<string, number | null>
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
    useState<Record<string, number | null>>(normalizeChartData(initialData))
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)
  const { suburbOptions, loading: suburbsLoading } = useSuburbsByState(
    filters.stateId
  )

  useEffect(() => {
    if (!hasSearched && Object.keys(normalizeChartData(initialData)).length) {
      setChartData(normalizeChartData(initialData))
    }
  }, [initialData, hasSearched])

  const fetchChart = async (nextFilters = filters) => {
    setLoading(true)
    setHasSearched(true)
    try {
      const data = await dashboardAPI.filterRatingAttributesChart(
        buildRatingAttributesPayload(nextFilters)
      )
      setChartData(normalizeChartData(data))
    } catch {
      setChartData({})
    } finally {
      setLoading(false)
    }
  }

  const companyOptions = useMemo(
    () => toSelectOptions(initialCompanies),
    [initialCompanies]
  )
  const stateOptions = useMemo(
    () => toSelectOptions(initialStates),
    [initialStates]
  )
  const scopedProjects = useMemo(() => {
    if (!filters.companyId) {
      return initialProjects
    }
    return initialProjects.filter(
      (project) =>
        project.developer_name != null &&
        String(project.developer_name) === filters.companyId
    )
  }, [initialProjects, filters.companyId])
  const projectOptions = useMemo(
    () => toPropertySelectOptions(scopedProjects),
    [scopedProjects]
  )
  const exportSubtitle = useMemo(
    () =>
      formatRatingAttributesExportFilters(filters, {
        companyOptions,
        stateOptions,
        suburbOptions,
        projectOptions,
      }),
    [filters, companyOptions, stateOptions, suburbOptions, projectOptions]
  )

  return (
    <DashboardSectionCard
      title="Rating Attributes"
      exportSubtitle={exportSubtitle}
      data-dashboard-export="rating-attributes"
      headerRight={
        <RatingAttributesFilter
          values={filters}
          companyOptions={companyOptions}
          stateOptions={stateOptions}
          suburbOptions={suburbOptions}
          projectOptions={projectOptions}
          loading={loading}
          suburbsLoading={suburbsLoading}
          onChange={setFilters}
          onSearch={() => void fetchChart()}
        />
      }
    >
      <div className="px-5 py-6">
        <RatingAttributesChart
          data={chartData}
          loading={loading}
          companySelected={Boolean(filters.companyId)}
        />
      </div>
    </DashboardSectionCard>
  )
}
