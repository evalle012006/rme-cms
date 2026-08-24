import { useMemo } from 'react'
import { Bar } from 'react-chartjs-2'
import type { ChartOptions } from 'chart.js'
import type { DashboardPropertyBar } from '@/api/dashboard/dashboardAPIHandler'
import {
  baseBarChartOptions,
  CHART_PRIMARY_LIGHT,
  CHART_TICK,
  chartAxisBorder,
  chartGridLines,
  chartTickFont,
  chartTickPadding,
  chartWrapperClassName,
  starTick,
} from '@/pages/dashboard/shared/chartSetup'

type PropertyReviewsChartProps = {
  data: DashboardPropertyBar[]
  loading?: boolean
}

export function PropertyReviewsChart({
  data,
  loading = false,
}: PropertyReviewsChartProps) {
  const chartConfig = useMemo(() => {
    const labels = data.map((item) => item.project_name)
    const values = data.map((item) =>
      Math.max(0, Math.min(5, item.overall_rating ?? 0))
    )

    return {
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: CHART_PRIMARY_LIGHT,
            borderRadius: 4,
            maxBarThickness: 48,
          },
        ],
      },
      options: {
        ...baseBarChartOptions,
        scales: {
          x: {
            offset: true,
            ticks: {
              color: CHART_TICK,
              font: chartTickFont,
              padding: chartTickPadding,
            },
            grid: {
              ...chartGridLines,
              display: true,
            },
            border: chartAxisBorder,
          },
          y: {
            min: 0,
            max: 5,
            ticks: {
              stepSize: 1,
              color: CHART_TICK,
              font: chartTickFont,
              padding: chartTickPadding,
              callback: (value) => starTick(value),
            },
            grid: {
              ...chartGridLines,
              display: true,
            },
            border: chartAxisBorder,
          },
        },
      } satisfies ChartOptions<'bar'>,
    }
  }, [data])

  if (loading) {
    return (
      <div className="flex min-h-[280px] items-center justify-center text-sm text-gray-500">
        Loading chart...
      </div>
    )
  }

  if (!data.length) {
    return (
      <div className="flex min-h-[280px] items-center justify-center text-sm text-gray-500">
        No project review data for the selected filters.
      </div>
    )
  }

  return (
    <div className={chartWrapperClassName}>
      <Bar data={chartConfig.data} options={chartConfig.options} />
    </div>
  )
}
