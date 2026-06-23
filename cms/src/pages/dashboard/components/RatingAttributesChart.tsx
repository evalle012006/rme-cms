import { useMemo } from 'react'
import { Bar } from 'react-chartjs-2'
import type { ChartOptions } from 'chart.js'
import {
  baseBarChartOptions,
  CHART_GRID,
  CHART_PRIMARY,
  CHART_TICK,
  chartLayoutPadding,
  chartTickFont,
  chartTickPadding,
  chartWrapperClassName,
  starTick,
} from '@/pages/dashboard/shared/chartSetup'

type RatingAttributesChartProps = {
  data: Record<string, number | null>
  loading?: boolean
}

export function RatingAttributesChart({
  data,
  loading = false,
}: RatingAttributesChartProps) {
  const entries = Object.entries(data)

  const chartConfig = useMemo(() => {
    const labels = entries.map(([name]) => name)
    const values = entries.map(([, rawValue]) =>
      Math.max(0, Math.min(5, rawValue ?? 0))
    )

    return {
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: CHART_PRIMARY,
            borderRadius: 2,
            barThickness: 14,
          },
        ],
      },
      options: {
        ...baseBarChartOptions,
        indexAxis: 'y',
        layout: {
          padding: {
            ...chartLayoutPadding,
            left: 28,
          },
        },
        scales: {
          x: {
            min: 0,
            max: 5,
            ticks: {
              stepSize: 1,
              color: CHART_TICK,
              font: chartTickFont,
              padding: chartTickPadding,
              callback: (value) => starTick(value),
            },
            grid: { color: CHART_GRID },
            border: { color: CHART_GRID },
          },
          y: {
            ticks: {
              color: CHART_TICK,
              font: chartTickFont,
              padding: chartTickPadding,
              autoSkip: false,
            },
            grid: { color: CHART_GRID },
            border: { color: CHART_GRID },
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

  if (!entries.length) {
    return (
      <div className="flex min-h-[280px] items-center justify-center text-sm text-gray-500">
        No rating attribute data for the selected filters.
      </div>
    )
  }

  return (
    <div className={chartWrapperClassName}>
      <Bar data={chartConfig.data} options={chartConfig.options} />
    </div>
  )
}
