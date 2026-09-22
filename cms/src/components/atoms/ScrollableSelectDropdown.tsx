import {
  useCallback,
  useEffect,
  useId,
  useLayoutEffect,
  useMemo,
  useRef,
  useState,
  type KeyboardEvent,
} from 'react'
import { createPortal } from 'react-dom'
import { ChevronDown } from 'lucide-react'
import { cn } from '@/lib/utils'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

type DropdownPosition = {
  top: number
  left: number
  width: number
  maxHeight: number
}

export type ScrollableSelectDropdownProps = {
  options: SelectDropdownOption[]
  value: string
  onChange: (value: string) => void
  placeholder?: string
  disabled?: boolean
  className?: string
  searchable?: boolean
  searchPlaceholder?: string
  loading?: boolean
  /**
   * When provided the parent owns filtering: `options` are rendered as given and
   * each keystroke is reported here. Used for server-side locality search.
   */
  onSearchChange?: (query: string) => void
  /** Shown in place of the option list when there is nothing to display. */
  emptyMessage?: string
}

export function ScrollableSelectDropdown({
  options,
  value,
  onChange,
  placeholder = 'Select…',
  disabled = false,
  className,
  searchable = false,
  searchPlaceholder = 'Search…',
  loading = false,
  onSearchChange,
  emptyMessage = 'No results',
}: ScrollableSelectDropdownProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const triggerRef = useRef<HTMLButtonElement>(null)
  const searchInputRef = useRef<HTMLInputElement>(null)
  const listRef = useRef<HTMLDivElement>(null)
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const [activeIndex, setActiveIndex] = useState(-1)
  const [dropdownPosition, setDropdownPosition] =
    useState<DropdownPosition | null>(null)

  const listboxId = useId()
  const externalSearch = typeof onSearchChange === 'function'

  const selectedLabel = useMemo(() => {
    if (!value) return null
    return options.find((option) => option.value === value)?.label ?? null
  }, [options, value])

  const filteredOptions = useMemo(() => {
    // The parent already filtered when it owns the search.
    if (externalSearch) return options
    const normalized = query.trim().toLowerCase()
    if (!normalized) return options
    return options.filter((option) =>
      option.label.toLowerCase().includes(normalized)
    )
  }, [externalSearch, options, query])

  // The clear row sits above the options and is reachable by keyboard, so it
  // takes index 0 and the options start at 1.
  const rowCount = filteredOptions.length + 1

  const updateDropdownPosition = useCallback(() => {
    const trigger = triggerRef.current
    if (!trigger) return

    const rect = trigger.getBoundingClientRect()
    const gap = 4
    // Searchable menus include a pinned search field, so they need extra height
    // to still show ~8–10 suburb rows.
    const preferredMax = searchable ? 320 : 224
    const spaceBelow = window.innerHeight - rect.bottom - 8
    const spaceAbove = rect.top - 8
    const openUp = spaceBelow < 200 && spaceAbove > spaceBelow
    const available = openUp ? spaceAbove : spaceBelow
    const maxHeight = Math.max(120, Math.min(preferredMax, available))

    setDropdownPosition({
      top: openUp ? rect.top - maxHeight - gap : rect.bottom + gap,
      left: rect.left,
      width: rect.width,
      maxHeight,
    })
  }, [searchable])

  useLayoutEffect(() => {
    if (!open) {
      setDropdownPosition(null)
      return
    }

    updateDropdownPosition()
    window.addEventListener('resize', updateDropdownPosition)
    window.addEventListener('scroll', updateDropdownPosition, true)

    return () => {
      window.removeEventListener('resize', updateDropdownPosition)
      window.removeEventListener('scroll', updateDropdownPosition, true)
    }
  }, [open, updateDropdownPosition])

  useEffect(() => {
    if (!open) return

    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as Node
      if (
        containerRef.current?.contains(target) ||
        (target instanceof Element &&
          target.closest('[data-scrollable-select-dropdown]'))
      ) {
        return
      }
      setOpen(false)
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [open])

  useEffect(() => {
    if (disabled || loading) setOpen(false)
  }, [disabled, loading])

  useEffect(() => {
    if (!open) {
      setQuery('')
      setActiveIndex(-1)
      if (externalSearch) onSearchChange?.('')
      return
    }
    if (searchable) {
      searchInputRef.current?.focus()
    }
    // Highlight the current selection so Enter re-confirms rather than jumping.
    const selectedIndex = filteredOptions.findIndex(
      (option) => option.value === value
    )
    setActiveIndex(selectedIndex >= 0 ? selectedIndex + 1 : 0)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, searchable])

  // Keep the highlight inside range as the option list changes under it.
  useEffect(() => {
    setActiveIndex((current) => (current >= rowCount ? rowCount - 1 : current))
  }, [rowCount])

  const scrollActiveIntoView = useCallback((index: number) => {
    const list = listRef.current
    if (!list) return
    const row = list.querySelector<HTMLElement>(`[data-row-index="${index}"]`)
    row?.scrollIntoView({ block: 'nearest' })
  }, [])

  const commit = useCallback(
    (index: number) => {
      if (index <= 0) {
        onChange('')
      } else {
        const option = filteredOptions[index - 1]
        if (!option) return
        onChange(option.value)
      }
      setOpen(false)
      triggerRef.current?.focus()
    },
    [filteredOptions, onChange]
  )

  /** ARIA combobox keyboard handling, shared by the trigger and search input. */
  const handleNavigationKeys = (
    event: KeyboardEvent<HTMLElement>,
    { openOnArrow }: { openOnArrow: boolean }
  ) => {
    if (disabled || loading) return

    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault()
        if (!open && openOnArrow) {
          setOpen(true)
          return
        }
        setActiveIndex((current) => {
          const next = current + 1 >= rowCount ? 0 : current + 1
          scrollActiveIntoView(next)
          return next
        })
        return
      case 'ArrowUp':
        event.preventDefault()
        if (!open && openOnArrow) {
          setOpen(true)
          return
        }
        setActiveIndex((current) => {
          const next = current - 1 < 0 ? rowCount - 1 : current - 1
          scrollActiveIntoView(next)
          return next
        })
        return
      case 'Home':
        if (!open) return
        event.preventDefault()
        setActiveIndex(0)
        scrollActiveIntoView(0)
        return
      case 'End':
        if (!open) return
        event.preventDefault()
        setActiveIndex(rowCount - 1)
        scrollActiveIntoView(rowCount - 1)
        return
      case 'Enter':
        if (!open) {
          event.preventDefault()
          setOpen(true)
          return
        }
        event.preventDefault()
        commit(activeIndex)
        return
      case 'Escape':
        if (!open) return
        event.preventDefault()
        event.stopPropagation()
        setOpen(false)
        triggerRef.current?.focus()
        return
      case 'Tab':
        setOpen(false)
        return
      default:
        return
    }
  }

  const handleTriggerKeyDown = (event: KeyboardEvent<HTMLButtonElement>) => {
    if (event.key === ' ') {
      event.preventDefault()
      setOpen((prev) => !prev)
      return
    }
    handleNavigationKeys(event, { openOnArrow: true })
  }

  const handleQueryChange = (next: string) => {
    setQuery(next)
    setActiveIndex(0)
    if (externalSearch) onSearchChange?.(next)
  }

  const rowId = (index: number) => `${listboxId}-row-${index}`

  const dropdown =
    open && !disabled && dropdownPosition
      ? createPortal(
          <div
            data-scrollable-select-dropdown
            style={{
              position: 'fixed',
              top: dropdownPosition.top,
              left: dropdownPosition.left,
              width: dropdownPosition.width,
              maxHeight: dropdownPosition.maxHeight,
            }}
            className="z-[9999] flex flex-col overflow-hidden rounded-card border border-gray-200 bg-white shadow-lg"
          >
            {searchable && (
              <div className="shrink-0 border-b border-gray-100 p-2">
                <input
                  ref={searchInputRef}
                  type="text"
                  role="combobox"
                  aria-expanded
                  aria-controls={listboxId}
                  aria-autocomplete="list"
                  aria-activedescendant={
                    activeIndex >= 0 ? rowId(activeIndex) : undefined
                  }
                  value={query}
                  placeholder={searchPlaceholder}
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none"
                  onChange={(event) => handleQueryChange(event.target.value)}
                  onKeyDown={(event) =>
                    handleNavigationKeys(event, { openOnArrow: false })
                  }
                />
              </div>
            )}
            <div
              ref={listRef}
              id={listboxId}
              role="listbox"
              className="min-h-0 flex-1 overflow-y-auto py-1"
            >
              <div
                role="option"
                id={rowId(0)}
                data-row-index={0}
                aria-selected={!value}
                onMouseEnter={() => setActiveIndex(0)}
                onClick={() => commit(0)}
                className={cn(
                  'cursor-pointer px-3 py-2 text-left text-sm',
                  activeIndex === 0 || !value
                    ? 'bg-primary-50 text-primary-700'
                    : 'text-gray-700'
                )}
              >
                {placeholder}
              </div>
              {filteredOptions.length === 0 ? (
                <p className="px-3 py-2 text-sm text-gray-400">
                  {loading ? 'Loading…' : emptyMessage}
                </p>
              ) : (
                filteredOptions.map((option, index) => {
                  const rowIndex = index + 1
                  const isSelected = value === option.value
                  const isActive = activeIndex === rowIndex
                  return (
                    <div
                      key={option.value}
                      role="option"
                      id={rowId(rowIndex)}
                      data-row-index={rowIndex}
                      aria-selected={isSelected}
                      onMouseEnter={() => setActiveIndex(rowIndex)}
                      onClick={() => commit(rowIndex)}
                      className={cn(
                        'cursor-pointer px-3 py-2 text-left text-sm',
                        isActive || isSelected
                          ? 'bg-primary-50 text-primary-700'
                          : 'text-gray-700'
                      )}
                    >
                      {option.label}
                    </div>
                  )
                })
              )}
            </div>
          </div>,
          document.body
        )
      : null

  return (
    <div ref={containerRef} className="relative w-full">
      <button
        ref={triggerRef}
        type="button"
        disabled={disabled || loading}
        role="combobox"
        aria-expanded={open}
        aria-haspopup="listbox"
        aria-controls={open ? listboxId : undefined}
        aria-activedescendant={
          open && !searchable && activeIndex >= 0 ? rowId(activeIndex) : undefined
        }
        onClick={() => {
          if (!disabled && !loading) setOpen((prev) => !prev)
        }}
        onKeyDown={handleTriggerKeyDown}
        className={cn(
          'flex w-full items-center justify-between rounded-card border border-gray-300 bg-white py-2.5 pr-3 pl-3.5 text-left text-sm transition',
          'focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none',
          'disabled:pointer-events-none',
          !loading && 'disabled:opacity-50',
          className
        )}
      >
        <span
          className={cn(
            'truncate',
            !selectedLabel && (loading ? 'text-gray-700' : 'text-gray-400')
          )}
        >
          {selectedLabel ?? placeholder}
        </span>
        <ChevronDown
          size={16}
          className={cn(
            'ml-2 shrink-0 text-gray-400 transition-transform',
            open && 'rotate-180'
          )}
        />
      </button>
      {dropdown}
    </div>
  )
}
