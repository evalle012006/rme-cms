import { useEffect, useLayoutEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Button } from '@/components/atoms/Button'
import { cn } from '@/lib/utils'
import {
  type DateRange,
  clampDateRangeToMaxDays,
  formatApiDate,
  formatDisplayDateRange,
  getCurrentMonthRange,
  getCurrentWeekRange,
  isDateSelectableForRangeEnd,
  isDateWithinRange,
  isSameApiDate,
  isSelectingRangeEnd,
  MAX_REVIEW_COUNT_RANGE_DAYS,
  parseApiDate,
} from '@/pages/dashboard/shared/dashboardUtils'

type ReviewCountDateRangePickerProps = {
  value: DateRange
  onApply: (range: DateRange) => void
}

type Preset = 'week' | 'month' | 'custom'

const WEEKDAY_LABELS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

function getMonthMatrix(year: number, month: number): Array<Date | null> {
  const firstDay = new Date(year, month, 1)
  const startOffset = firstDay.getDay()
  const daysInMonth = new Date(year, month + 1, 0).getDate()

  const cells: Array<Date | null> = []
  for (let index = 0; index < startOffset; index += 1) cells.push(null)
  for (let day = 1; day <= daysInMonth; day += 1) {
    cells.push(new Date(year, month, day))
  }
  while (cells.length % 7 !== 0) cells.push(null)

  return cells
}

function MonthCalendar({
  year,
  month,
  draftRange,
  selectingEnd,
  onSelectDate,
}: {
  year: number
  month: number
  draftRange: DateRange
  selectingEnd: boolean
  onSelectDate: (date: Date) => void
}) {
  const monthLabel = new Date(year, month, 1).toLocaleString('en-AU', {
    month: 'short',
    year: 'numeric',
  })
  const cells = getMonthMatrix(year, month)
  const anchorDate = parseApiDate(draftRange.from)

  return (
    <div className="min-w-[220px]">
      <p className="mb-3 text-center text-sm font-semibold text-gray-700">
        {monthLabel}
      </p>
      <div className="mb-2 grid grid-cols-7 gap-1 text-center text-xs text-gray-400">
        {WEEKDAY_LABELS.map((label) => (
          <span key={label}>{label}</span>
        ))}
      </div>
      <div className="grid grid-cols-7 gap-1">
        {cells.map((date, index) => {
          if (!date) {
            return <span key={`empty-${index}`} />
          }

          const inRange = isDateWithinRange(date, draftRange)
          const isStart = isSameApiDate(date, parseApiDate(draftRange.from))
          const isEnd = isSameApiDate(date, parseApiDate(draftRange.to))
          const isDisabled =
            selectingEnd &&
            !isDateSelectableForRangeEnd(date, anchorDate)

          return (
            <button
              key={formatApiDate(date)}
              type="button"
              disabled={isDisabled}
              onClick={() => onSelectDate(date)}
              className={cn(
                'h-8 rounded-full text-sm transition',
                isDisabled
                  ? 'cursor-not-allowed text-gray-300'
                  : 'text-gray-700 hover:bg-primary-50',
                !isDisabled && inRange && 'bg-primary-100',
                !isDisabled &&
                  (isStart || isEnd) &&
                  'bg-primary-500 text-white hover:bg-primary-600'
              )}
            >
              {date.getDate()}
            </button>
          )
        })}
      </div>
    </div>
  )
}

export function ReviewCountDateRangePicker({
  value,
  onApply,
}: ReviewCountDateRangePickerProps) {
  const [open, setOpen] = useState(false)
  const [draftRange, setDraftRange] = useState<DateRange>(value)
  const [activePreset, setActivePreset] = useState<Preset>('week')
  const [viewDate, setViewDate] = useState(() => parseApiDate(value.from))
  const panelRef = useRef<HTMLDivElement>(null)
  const triggerRef = useRef<HTMLButtonElement>(null)
  const positionRafRef = useRef(0)

  const selectingEnd = isSelectingRangeEnd(draftRange)

  const updatePanelPosition = () => {
    if (!triggerRef.current || !panelRef.current) return

    const rect = triggerRef.current.getBoundingClientRect()
    const width = Math.min(720, window.innerWidth - 32)
    const left = Math.max(
      16,
      Math.min(rect.right - width, window.innerWidth - width - 16)
    )

    const panel = panelRef.current
    panel.style.top = `${rect.bottom + 8}px`
    panel.style.left = `${left}px`
    panel.style.width = `${width}px`
  }

  const schedulePanelPositionUpdate = () => {
    cancelAnimationFrame(positionRafRef.current)
    positionRafRef.current = requestAnimationFrame(updatePanelPosition)
  }

  useEffect(() => {
    if (!open) setDraftRange(value)
  }, [open, value])

  useLayoutEffect(() => {
    if (!open) return

    updatePanelPosition()

    window.addEventListener('resize', schedulePanelPositionUpdate)
    window.addEventListener('scroll', schedulePanelPositionUpdate, true)

    return () => {
      cancelAnimationFrame(positionRafRef.current)
      window.removeEventListener('resize', schedulePanelPositionUpdate)
      window.removeEventListener('scroll', schedulePanelPositionUpdate, true)
    }
  }, [open])

  useLayoutEffect(() => {
    if (!open) return
    schedulePanelPositionUpdate()
  }, [open, selectingEnd, draftRange.from, draftRange.to, viewDate])

  useEffect(() => {
    if (!open) return

    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as Node
      if (
        panelRef.current?.contains(target) ||
        triggerRef.current?.contains(target)
      ) {
        return
      }
      setOpen(false)
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [open])

  const applyPreset = (preset: Preset) => {
    const nextRange =
      preset === 'week' ? getCurrentWeekRange() : getCurrentMonthRange()

    setActivePreset(preset)
    setDraftRange(nextRange)
    setViewDate(parseApiDate(nextRange.from))
  }

  const handleSelectDate = (date: Date) => {
    setActivePreset('custom')
    const selected = formatApiDate(date)

    setDraftRange((current) => {
      const isAnchorOnly = isSelectingRangeEnd(current)

      if (!isAnchorOnly) {
        return { from: selected, to: selected }
      }

      if (selected === current.from) {
        return current
      }

      if (!isDateSelectableForRangeEnd(date, parseApiDate(current.from))) {
        return current
      }

      if (selected < current.from) {
        return { from: selected, to: current.from }
      }

      return { from: current.from, to: selected }
    })
  }

  const handleApply = () => {
    onApply(clampDateRangeToMaxDays(draftRange))
    setOpen(false)
  }

  const leftMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1)
  const rightMonth = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1)

  const panel = open && (
    <div
      ref={panelRef}
      className="fixed z-50 rounded-card border border-gray-200 bg-white shadow-lg"
      style={{ maxHeight: 'calc(100vh - 24px)' }}
    >
      <div className="flex max-h-[calc(100vh-120px)] overflow-y-auto">
        <div className="flex w-36 shrink-0 flex-col gap-1 border-r border-gray-200 p-4">
          <button
            type="button"
            onClick={() => applyPreset('week')}
            className={cn(
              'rounded-card px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100',
              activePreset === 'week' && 'bg-gray-100 font-medium'
            )}
          >
            This Week
          </button>
          <button
            type="button"
            onClick={() => applyPreset('month')}
            className={cn(
              'rounded-card px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100',
              activePreset === 'month' && 'bg-gray-100 font-medium'
            )}
          >
            This Month
          </button>
        </div>

        <div className="min-w-0 flex-1 p-4">
          <div className="mb-4 flex items-center justify-between">
            <button
              type="button"
              onClick={() =>
                setViewDate(
                  new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1)
                )
              }
              className="rounded-full p-1 text-gray-500 hover:bg-gray-100"
              aria-label="Previous month"
            >
              <ChevronLeft size={18} />
            </button>
            <button
              type="button"
              onClick={() =>
                setViewDate(
                  new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1)
                )
              }
              className="rounded-full p-1 text-gray-500 hover:bg-gray-100"
              aria-label="Next month"
            >
              <ChevronRight size={18} />
            </button>
          </div>

          <div className="grid gap-6 md:grid-cols-2">
            <MonthCalendar
              year={leftMonth.getFullYear()}
              month={leftMonth.getMonth()}
              draftRange={draftRange}
              selectingEnd={selectingEnd}
              onSelectDate={handleSelectDate}
            />
            <MonthCalendar
              year={rightMonth.getFullYear()}
              month={rightMonth.getMonth()}
              draftRange={draftRange}
              selectingEnd={selectingEnd}
              onSelectDate={handleSelectDate}
            />
          </div>

          {selectingEnd && (
            <p className="mt-3 text-xs text-gray-500">
              Select an end date within {MAX_REVIEW_COUNT_RANGE_DAYS} days of{' '}
              {formatDisplayDateRange(draftRange.from, draftRange.from)}.
            </p>
          )}
        </div>
      </div>

      <div className="flex shrink-0 items-center justify-between border-t border-gray-200 bg-white px-4 py-4">
        <span className="text-sm text-gray-600">
          {formatDisplayDateRange(draftRange.from, draftRange.to)}
        </span>
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={() => setOpen(false)}
            className="text-sm text-gray-500 hover:text-gray-700"
          >
            Cancel
          </button>
          <Button
            color="primary"
            className="h-auto shrink-0 px-5 py-2.5"
            onClick={handleApply}
          >
            Apply
          </Button>
        </div>
      </div>
    </div>
  )

  return (
    <div className="relative">
      <button
        ref={triggerRef}
        type="button"
        data-dashboard-export-date-range
        data-dashboard-export-subtitle
        onClick={() => setOpen((current) => !current)}
        className="rounded-card border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
      >
        {formatDisplayDateRange(value.from, value.to)}
      </button>

      {panel && createPortal(panel, document.body)}
    </div>
  )
}
