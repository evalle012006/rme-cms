import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
  type ChartOptions,
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  Tooltip
)

export const CHART_PRIMARY = '#0694a2'
export const CHART_PRIMARY_LIGHT = '#16bdca'
export const CHART_GRID = '#c5cdd8'
export const CHART_TICK = '#374151'

export const chartGridLines = {
  color: CHART_GRID,
  lineWidth: 1,
  drawOnChartArea: true,
  drawTicks: true,
} as const

export const chartAxisBorder = {
  color: CHART_GRID,
  width: 1,
} as const

export const chartWrapperClassName = 'chart-wrapper h-[280px] w-full'

export const chartTickFont = {
  size: 12,
  weight: 500,
}

export const chartLayoutPadding = {
  top: 18,
  right: 22,
  bottom: 18,
  left: 20,
}

export const chartTickPadding = 10

export function starTick(value: string | number) {
  return `${value} star`
}

const baseChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  layout: {
    padding: chartLayoutPadding,
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1f2937',
      padding: 10,
      cornerRadius: 6,
    },
  },
} as const

export const baseBarChartOptions: ChartOptions<'bar'> = baseChartOptions
export const baseLineChartOptions: ChartOptions<'line'> = baseChartOptions
