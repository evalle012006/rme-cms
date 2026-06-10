import { useCallback, useEffect, useMemo, useState } from 'react'
import { dashboardAPI } from '@/api/dashboard/dashboardAPIHandler'
import { DashboardSectionCard } from '@/pages/dashboard/shared/DashboardSectionCard'
import {
  getChartLabelsInRange,
  getCurrentWeekRange,
  toReviewCountChartPayload,
  type DateRange,
} from '@/pages/dashboard/shared/dashboardUtils'
import { ReviewCountDateRangePicker } from './ReviewCountDateRangePicker'
import { ReviewCountChart } from './ReviewCountChart'

export function ReviewCountSection() {
  const [range, setRange] = useState<DateRange>(getCurrentWeekRange)
  const [chartData, setChartData] = useState<Record<string, number>>({})
  const [loading, setLoading] = useState(true)

  const fetchChart = useCallback(async (nextRange: DateRange) => {
    setLoading(true)
    try {
      const data = await dashboardAPI.reviewCountChart(
        toReviewCountChartPayload(nextRange),
        { showErrorToast: false }
      )
      setChartData(data)
    } catch {
      setChartData({})
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    void fetchChart(range)
  }, [fetchChart, range])

  const chartLabels = useMemo(() => {
    const responseLabels = Object.keys(chartData)
    if (responseLabels.length > 0) return responseLabels
    return getChartLabelsInRange(range.from, range.to)
  }, [chartData, range.from, range.to])

  const values = useMemo(
    () => chartLabels.map((label) => chartData[label] ?? 0),
    [chartLabels, chartData]
  )

  return (
    <DashboardSectionCard
      className="overflow-visible"
      title="Review Count"
      headerRight={
        <ReviewCountDateRangePicker
          value={range}
          onApply={(nextRange) => setRange(nextRange)}
        />
      }
    >
      <div className="px-5 py-6">
        {loading ? (
          <div className="flex min-h-[280px] items-center justify-center text-sm text-gray-500">
            Loading chart...
          </div>
        ) : (
          <ReviewCountChart labels={chartLabels} values={values} />
        )}
      </div>
    </DashboardSectionCard>
  )
}
