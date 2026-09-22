import { useEffect, useMemo, useState } from 'react'
import {
  dashboardAPI,
  type DashboardCompany,
  type DashboardPropertyOption,
} from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  buildRatingAttributesPayload,
  formatRatingAttributesExportFilters,
  toPropertySelectOptions,
  toSelectOptions,
} from '@/pages/dashboard/shared/dashboardUtils'
import { EMPTY_LOCALITY_SELECTION } from '@/components/atoms/LocalitySelect'
import {
  RatingAttributesFilter,
  type RatingAttributesFilterValues,
} from './RatingAttributesFilter'
import { RatingAttributesChart } from './RatingAttributesChart'

type RatingAttributesSectionProps = {
  initialData?: Record<string, number | null>
  initialCompanies?: DashboardCompany[]
  initialProjects?: DashboardPropertyOption[]
}

const defaultFilters: RatingAttributesFilterValues = {
  companyId: '',
  locality: EMPTY_LOCALITY_SELECTION,
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
  initialProjects = [],
}: RatingAttributesSectionProps) {
  const [filters, setFilters] =
    useState<RatingAttributesFilterValues>(defaultFilters)
  const [chartData, setChartData] =
    useState<Record<string, number | null>>(normalizeChartData(initialData))
  const [loading, setLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)

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
        projectOptions,
      }),
    [filters, companyOptions, projectOptions]
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
          projectOptions={projectOptions}
          loading={loading}
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
