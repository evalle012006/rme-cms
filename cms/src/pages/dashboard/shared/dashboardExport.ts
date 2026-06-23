import { Chart } from 'chart.js'
import { jsPDF } from 'jspdf'

const PAGE_WIDTH_MM = 210
const PAGE_HEIGHT_MM = 297
const MARGIN_MM = 14
const CONTENT_WIDTH_MM = PAGE_WIDTH_MM - MARGIN_MM * 2
const STAT_CARD_HEIGHT_MM = 28
const STAT_CARD_GAP_MM = 4
const STAT_ACCENT_SIZE_MM = 12
const HEADER_BLOCK_HEIGHT_MM = 14
const STATS_BOTTOM_GAP_MM = 6
const SECTION_HEADER_HEIGHT_MM = 12
const SECTION_HEADER_WITH_SUBTITLE_MM = 20
const SECTION_SUBTITLE_LINE_HEIGHT_MM = 4
const EXPORT_SUBTITLE_SELECTOR = '[data-dashboard-export-subtitle]'
const SECTION_BODY_GAP_MM = 4
const SECTION_BOTTOM_GAP_MM = 4
const SECTION_GAP_MM = 6
const MAX_CHART_HEIGHT_MM = 58
const CHART_EXPORT_SCALE = 3
const CHART_BODY_INSET_MM = 5
const CHART_EXPORT_VALUE_TICK_FONT = {
  size: 50,
  weight: 400,
}
const CHART_EXPORT_CATEGORY_TICK_FONT = {
  size: 50,
  weight: 400,
}
const CHART_EXPORT_VALUE_TICK_PADDING = 14
const CHART_EXPORT_CATEGORY_TICK_PADDING = 16
const CHART_EXPORT_TICK_COLOR = '#111827'

type TickOptionsSnapshot = {
  font?: Record<string, unknown> | unknown
  padding?: number
  color?: unknown
}

type ChartExportStyleSnapshot = {
  layoutPadding?: number | Record<string, number>
  scaleTicks: Record<string, TickOptionsSnapshot>
}

export type DashboardExportStat = {
  label: string
  count: number | string
  accentColor: string
}

export type DashboardExportSection = {
  title: string
  subtitle?: string
  chartImage?: string | null
  emptyMessage?: string
}

export type DashboardExportSnapshot = {
  exportedAt: string
  stats: DashboardExportStat[]
  sections: DashboardExportSection[]
}

const EXPORT_SECTIONS = [
  {
    key: 'property-reviews',
    title: 'Property Reviews',
  },
  {
    key: 'review-count',
    title: 'Review Count',
  },
  {
    key: 'rating-attributes',
    title: 'Rating Attributes',
  },
] as const

type PdfContext = {
  pdf: jsPDF
  y: number
}

function hexToRgb(hex: string): [number, number, number] {
  const normalized = hex.replace('#', '')
  if (normalized.length !== 6) {
    return [20, 148, 136]
  }

  return [
    Number.parseInt(normalized.slice(0, 2), 16),
    Number.parseInt(normalized.slice(2, 4), 16),
    Number.parseInt(normalized.slice(4, 6), 16),
  ]
}

function getImageFormat(dataUrl: string): 'JPEG' | 'PNG' {
  return dataUrl.startsWith('data:image/png') ? 'PNG' : 'JPEG'
}

function canvasToPngWithWhiteBackground(canvas: HTMLCanvasElement): string {
  const output = document.createElement('canvas')
  output.width = canvas.width
  output.height = canvas.height
  const context = output.getContext('2d')

  if (!context) {
    return canvas.toDataURL('image/png', 1)
  }

  context.fillStyle = '#ffffff'
  context.fillRect(0, 0, output.width, output.height)
  context.drawImage(canvas, 0, 0)
  return output.toDataURL('image/png', 1)
}

function waitForNextFrame(): Promise<void> {
  return new Promise((resolve) => {
    requestAnimationFrame(() => resolve())
  })
}

function cloneLayoutPadding(
  padding: number | Record<string, number> | undefined
): number | Record<string, number> | undefined {
  if (padding === undefined) {
    return undefined
  }

  if (typeof padding === 'number') {
    return padding
  }

  return { ...padding }
}

function snapshotChartExportStyles(chart: Chart): ChartExportStyleSnapshot {
  const scaleTicks: Record<string, TickOptionsSnapshot> = {}
  const scales = chart.options.scales

  if (scales) {
    for (const [key, scale] of Object.entries(scales)) {
      const ticks =
        scale && typeof scale === 'object' && 'ticks' in scale
          ? (scale.ticks as TickOptionsSnapshot | undefined)
          : undefined

      if (!ticks) {
        continue
      }

      const font =
        ticks.font && typeof ticks.font === 'object'
          ? { ...(ticks.font as Record<string, unknown>) }
          : ticks.font

      scaleTicks[key] = {
        font,
        padding: ticks.padding,
        color: ticks.color,
      }
    }
  }

  const layout = chart.options.layout
  const layoutPadding =
    layout && typeof layout === 'object' && 'padding' in layout
      ? cloneLayoutPadding(
          layout.padding as number | Record<string, number> | undefined
        )
      : undefined

  return { layoutPadding, scaleTicks }
}


function isCategoryAxisScale(chart: Chart, scaleKey: string): boolean {
  return chart.options.indexAxis === 'y' ? scaleKey === 'y' : scaleKey === 'x'
}

function applyChartExportStyles(chart: Chart): void {
  const isHorizontalBar = chart.options.indexAxis === 'y'
  const layout = chart.options.layout
  if (layout && typeof layout === 'object') {
    layout.padding = {
      top: 24,
      right: 28,
      bottom: 28,
      left: isHorizontalBar ? 52 : 30,
    }
  }

  const scales = chart.options.scales
  if (!scales) {
    return
  }

  for (const [scaleKey, scale] of Object.entries(scales)) {
    if (
      !scale ||
      typeof scale !== 'object' ||
      !('ticks' in scale) ||
      !scale.ticks
    ) {
      continue
    }

    const isCategoryAxis = isCategoryAxisScale(chart, scaleKey)
    const ticks = scale.ticks as TickOptionsSnapshot
    ticks.font = {
      ...(isCategoryAxis
        ? CHART_EXPORT_CATEGORY_TICK_FONT
        : CHART_EXPORT_VALUE_TICK_FONT),
    }
    ticks.padding = isCategoryAxis
      ? CHART_EXPORT_CATEGORY_TICK_PADDING
      : CHART_EXPORT_VALUE_TICK_PADDING
    ticks.color = CHART_EXPORT_TICK_COLOR
  }
}

function restoreChartExportStyles(
  chart: Chart,
  snapshot: ChartExportStyleSnapshot
): void {
  const layout = chart.options.layout
  if (layout && typeof layout === 'object') {
    layout.padding = snapshot.layoutPadding
  }

  const scales = chart.options.scales
  if (!scales) {
    return
  }

  for (const [key, saved] of Object.entries(snapshot.scaleTicks)) {
    const scale = scales[key]
    if (
      !scale ||
      typeof scale !== 'object' ||
      !('ticks' in scale) ||
      !scale.ticks
    ) {
      continue
    }

    const ticks = scale.ticks as TickOptionsSnapshot
    ticks.font = saved.font
    ticks.padding = saved.padding
    ticks.color = saved.color
  }
}

async function prepareChartImage(source: HTMLCanvasElement): Promise<string> {
  const chart = Chart.getChart(source)

  if (chart) {
    const savedWidth = chart.width
    const savedHeight = chart.height
    const styleSnapshot = snapshotChartExportStyles(chart)
    const exportWidth = Math.max(1, Math.round(savedWidth * CHART_EXPORT_SCALE))
    const exportHeight = Math.max(
      1,
      Math.round(savedHeight * CHART_EXPORT_SCALE)
    )

    try {
      applyChartExportStyles(chart)
      chart.update('none')
      chart.resize(exportWidth, exportHeight)
      await waitForNextFrame()
      await waitForNextFrame()
      return canvasToPngWithWhiteBackground(source)
    } finally {
      restoreChartExportStyles(chart, styleSnapshot)
      chart.resize(savedWidth, savedHeight)
      chart.update('none')
    }
  }

  const canvas = document.createElement('canvas')
  canvas.width = Math.max(1, Math.round(source.width * CHART_EXPORT_SCALE))
  canvas.height = Math.max(1, Math.round(source.height * CHART_EXPORT_SCALE))
  const context = canvas.getContext('2d')

  if (!context) {
    return source.toDataURL('image/png', 1)
  }

  context.fillStyle = '#ffffff'
  context.fillRect(0, 0, canvas.width, canvas.height)
  context.imageSmoothingEnabled = true
  context.imageSmoothingQuality = 'high'
  context.drawImage(source, 0, 0, canvas.width, canvas.height)
  return canvas.toDataURL('image/png', 1)
}

function getImageSize(
  dataUrl: string
): Promise<{ width: number; height: number }> {
  return new Promise((resolve, reject) => {
    const image = new Image()
    image.onload = () => {
      resolve({ width: image.width, height: image.height })
    }
    image.onerror = () => reject(new Error('EXPORT_IMAGE_LOAD_FAILED'))
    image.src = dataUrl
  })
}

async function captureChartFromSection(sectionEl: Element | null): Promise<{
  chartImage?: string
  emptyMessage?: string
}> {
  if (!sectionEl) {
    return { emptyMessage: 'Section not available.' }
  }

  const canvas = sectionEl.querySelector('canvas')
  if (canvas instanceof HTMLCanvasElement) {
    try {
      const chartImage = await prepareChartImage(canvas)
      return { chartImage }
    } catch {
      return { emptyMessage: 'Could not capture chart image.' }
    }
  }

  const emptyMessage =
    sectionEl
      .querySelector('[data-dashboard-export-empty]')
      ?.textContent?.trim() ||
    sectionEl.querySelector('.text-gray-500')?.textContent?.trim()

  return { emptyMessage: emptyMessage || 'No data available.' }
}

export async function captureDashboardSnapshot(
  stats: DashboardExportStat[]
): Promise<DashboardExportSnapshot> {
  const sections = await Promise.all(
    EXPORT_SECTIONS.map(async (section) => {
      const sectionEl = document.querySelector(
        `[data-dashboard-export="${section.key}"]`
      )
      const subtitle = sectionEl
        ?.querySelector(EXPORT_SUBTITLE_SELECTOR)
        ?.textContent?.trim()
      const capture = await captureChartFromSection(sectionEl)

      return {
        title: section.title,
        subtitle,
        chartImage: capture.chartImage,
        emptyMessage: capture.emptyMessage,
      }
    })
  )

  return {
    exportedAt: new Date().toLocaleString('en-AU', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }),
    stats,
    sections,
  }
}

function ensureSpace(ctx: PdfContext, requiredHeight: number): void {
  if (ctx.y + requiredHeight <= PAGE_HEIGHT_MM - MARGIN_MM) {
    return
  }

  ctx.pdf.addPage()
  ctx.y = MARGIN_MM
}

function drawHeader(ctx: PdfContext, exportedAt: string): void {
  ctx.pdf.setFont('helvetica', 'bold')
  ctx.pdf.setFontSize(18)
  ctx.pdf.setTextColor(55, 65, 81)
  ctx.pdf.text('Dashboard Export', MARGIN_MM, ctx.y)

  ctx.pdf.setFont('helvetica', 'normal')
  ctx.pdf.setFontSize(10)
  ctx.pdf.setTextColor(107, 114, 128)
  ctx.pdf.text(`Generated ${exportedAt}`, MARGIN_MM, ctx.y + 7)

  ctx.y += HEADER_BLOCK_HEIGHT_MM
}

function drawStatCards(ctx: PdfContext, stats: DashboardExportStat[]): void {
  const cardWidth =
    (CONTENT_WIDTH_MM - STAT_CARD_GAP_MM * (stats.length - 1)) / stats.length
  const cardTop = ctx.y

  stats.forEach((stat, index) => {
    const x = MARGIN_MM + index * (cardWidth + STAT_CARD_GAP_MM)
    const [red, green, blue] = hexToRgb(stat.accentColor)
    const accentY =
      cardTop + (STAT_CARD_HEIGHT_MM - STAT_ACCENT_SIZE_MM) / 2
    const textX = x + 4 + STAT_ACCENT_SIZE_MM + 4
    const textCenterY = cardTop + STAT_CARD_HEIGHT_MM / 2

    ctx.pdf.setDrawColor(209, 213, 219)
    ctx.pdf.setFillColor(255, 255, 255)
    ctx.pdf.roundedRect(x, cardTop, cardWidth, STAT_CARD_HEIGHT_MM, 2, 2, 'FD')

    ctx.pdf.setFillColor(red, green, blue)
    ctx.pdf.roundedRect(
      x + 4,
      accentY,
      STAT_ACCENT_SIZE_MM,
      STAT_ACCENT_SIZE_MM,
      2,
      2,
      'F'
    )

    ctx.pdf.setFont('helvetica', 'bold')
    ctx.pdf.setFontSize(15)
    ctx.pdf.setTextColor(red, green, blue)
    ctx.pdf.text(String(stat.count), textX, textCenterY - 1.5)

    ctx.pdf.setFont('helvetica', 'bold')
    ctx.pdf.setFontSize(7)
    ctx.pdf.setTextColor(107, 114, 128)
    ctx.pdf.text(stat.label.toUpperCase(), textX, textCenterY + 4.5)
  })

  ctx.y += STAT_CARD_HEIGHT_MM + STATS_BOTTOM_GAP_MM
}

function getSectionHeaderHeight(section: DashboardExportSection): number {
  return section.subtitle
    ? SECTION_HEADER_WITH_SUBTITLE_MM
    : SECTION_HEADER_HEIGHT_MM
}

async function getChartDimensions(
  chartImage: string
): Promise<{ width: number; height: number }> {
  const size = await getImageSize(chartImage)
  const imageWidth = CONTENT_WIDTH_MM - 8 - CHART_BODY_INSET_MM * 2
  const naturalHeight = (size.height * imageWidth) / size.width
  const imageHeight = Math.min(naturalHeight, MAX_CHART_HEIGHT_MM)

  return { width: imageWidth, height: imageHeight }
}

async function estimateSectionHeight(
  section: DashboardExportSection
): Promise<number> {
  const headerHeight = getSectionHeaderHeight(section)

  if (!section.chartImage) {
    return headerHeight + 28 + SECTION_BOTTOM_GAP_MM
  }

  const { height } = await getChartDimensions(section.chartImage)

  return (
    headerHeight +
    SECTION_BODY_GAP_MM +
    height +
    CHART_BODY_INSET_MM * 2 +
    SECTION_BOTTOM_GAP_MM
  )
}

async function drawSection(
  ctx: PdfContext,
  section: DashboardExportSection
): Promise<void> {
  const sectionHeight = await estimateSectionHeight(section)
  ensureSpace(ctx, sectionHeight)

  const cardTop = ctx.y
  const headerHeight = getSectionHeaderHeight(section)

  ctx.pdf.setDrawColor(209, 213, 219)
  ctx.pdf.setFillColor(249, 250, 251)
  ctx.pdf.rect(MARGIN_MM, cardTop, CONTENT_WIDTH_MM, headerHeight, 'F')
  ctx.pdf.line(
    MARGIN_MM,
    cardTop + headerHeight,
    MARGIN_MM + CONTENT_WIDTH_MM,
    cardTop + headerHeight
  )

  ctx.pdf.setFont('helvetica', 'bold')
  ctx.pdf.setFontSize(11)
  ctx.pdf.setTextColor(55, 65, 81)
  ctx.pdf.text(section.title, MARGIN_MM + 4, cardTop + 8)

  if (section.subtitle) {
    ctx.pdf.setFont('helvetica', 'normal')
    ctx.pdf.setFontSize(9)
    ctx.pdf.setTextColor(107, 114, 128)
    const subtitleLines = ctx.pdf
      .splitTextToSize(section.subtitle, CONTENT_WIDTH_MM - 8)
      .slice(0, 2)
    subtitleLines.forEach((line: string, index: number) => {
      ctx.pdf.text(line, MARGIN_MM + 4, cardTop + 13 + index * SECTION_SUBTITLE_LINE_HEIGHT_MM)
    })
  }

  const bodyTop = cardTop + headerHeight + SECTION_BODY_GAP_MM

  if (section.chartImage) {
    const { width: imageWidth, height: imageHeight } =
      await getChartDimensions(section.chartImage)

    const imageX = MARGIN_MM + 4 + CHART_BODY_INSET_MM
    const imageAreaHeight = imageHeight + CHART_BODY_INSET_MM * 2

    ctx.pdf.setFillColor(255, 255, 255)
    ctx.pdf.rect(
      MARGIN_MM + 4,
      bodyTop,
      CONTENT_WIDTH_MM - 8,
      imageAreaHeight,
      'F'
    )

    ctx.pdf.addImage(
      section.chartImage,
      getImageFormat(section.chartImage),
      imageX,
      bodyTop + CHART_BODY_INSET_MM,
      imageWidth,
      imageHeight,
      undefined,
      'SLOW'
    )

    ctx.pdf.rect(
      MARGIN_MM,
      cardTop,
      CONTENT_WIDTH_MM,
      headerHeight + SECTION_BODY_GAP_MM + imageAreaHeight + SECTION_BOTTOM_GAP_MM,
      'S'
    )

    ctx.y = bodyTop + imageAreaHeight + SECTION_BOTTOM_GAP_MM
    return
  }

  ctx.pdf.rect(MARGIN_MM, cardTop, CONTENT_WIDTH_MM, headerHeight + 34, 'S')
  ctx.pdf.setFont('helvetica', 'normal')
  ctx.pdf.setFontSize(10)
  ctx.pdf.setTextColor(107, 114, 128)
  ctx.pdf.text(
    section.emptyMessage ?? 'No data available.',
    MARGIN_MM + 4,
    bodyTop + 8
  )

  ctx.y = bodyTop + 28
}

async function buildDashboardPdfAsync(
  snapshot: DashboardExportSnapshot
): Promise<jsPDF> {
  const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' })
  const ctx: PdfContext = { pdf, y: MARGIN_MM }

  drawHeader(ctx, snapshot.exportedAt)
  drawStatCards(ctx, snapshot.stats)

  for (let index = 0; index < snapshot.sections.length; index += 1) {
    const section = snapshot.sections[index]
    if (!section) {
      continue
    }

    if (index > 0) {
      ctx.y += SECTION_GAP_MM
    }

    await drawSection(ctx, section)
  }

  return pdf
}

export async function exportDashboardPdf(
  snapshot: DashboardExportSnapshot
): Promise<void> {
  const pdf = await buildDashboardPdfAsync(snapshot)
  const fileDate = new Date().toISOString().slice(0, 10)
  pdf.save(`dashboard-export-${fileDate}.pdf`)
}
