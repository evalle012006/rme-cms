import { useMemo } from 'react'
import { Line } from 'react-chartjs-2'
import type { ChartOptions } from 'chart.js'
import {
  baseLineChartOptions,
  CHART_PRIMARY,
  CHART_TICK,
  chartAxisBorder,
  chartGridLines,
  chartWrapperClassName,
} from '@/pages/dashboard/shared/chartSetup'
import { getMaxChartValue } from '@/pages/dashboard/shared/dashboardUtils'

type ReviewCountChartProps = {
  labels: string[]
  values: number[]
}

export function ReviewCountChart({ labels, values }: ReviewCountChartProps) {
  const chartConfig = useMemo(() => {
    const maxValue = getMaxChartValue(values)

    return {
      data: {
        labels,
        datasets: [
          {
            data: values,
            borderColor: CHART_PRIMARY,
            backgroundColor: CHART_PRIMARY,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: CHART_PRIMARY,
            tension: 0.2,
            fill: false,
          },
        ],
      },
      options: {
        ...baseLineChartOptions,
        scales: {
          x: {
            offset: false,
            ticks: {
              color: CHART_TICK,
              maxRotation: 45,
              minRotation: 45,
              autoSkip: labels.length > 20,
            },
            grid: {
              ...chartGridLines,
              display: true,
            },
            border: chartAxisBorder,
          },
          y: {
            min: 0,
            max: maxValue,
            ticks: {
              stepSize: 1,
              color: CHART_TICK,
            },
            grid: {
              ...chartGridLines,
              display: true,
            },
            border: chartAxisBorder,
          },
        },
      } satisfies ChartOptions<'line'>,
    }
  }, [labels, values])

  return (
    <div className={chartWrapperClassName}>
      <Line data={chartConfig.data} options={chartConfig.options} />
    </div>
  )
}
