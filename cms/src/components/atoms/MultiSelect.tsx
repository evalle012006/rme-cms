import {
  useCallback,
  useEffect,
  useLayoutEffect,
  useMemo,
  useRef,
  useState,
  type KeyboardEvent,
} from 'react'
import { createPortal } from 'react-dom'
import { ChevronDown, X } from 'lucide-react'
import { cn } from '@/lib/utils'

export type MultiSelectOption = {
  value: number
  label: string
}

export type MultiSelectProps = {
  options: MultiSelectOption[]
  value: number[]
  onChange: (value: number[]) => void
  disabled?: boolean
  placeholder?: string
  className?: string
  onOpenChange?: (open: boolean) => void
}

type DropdownPosition = {
  top: number
  left: number
  width: number
}

export function MultiSelect({
  options,
  value,
  onChange,
  disabled = false,
  placeholder = 'Select ratings…',
  className,
  onOpenChange,
}: MultiSelectProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const triggerRef = useRef<HTMLDivElement>(null)
  const [open, setOpen] = useState(false)
  const [dropdownPosition, setDropdownPosition] = useState<DropdownPosition | null>(
    null
  )

  const optionMap = useMemo(() => {
    const map = new Map<number, MultiSelectOption>()
    options.forEach((opt) => map.set(opt.value, opt))
    return map
  }, [options])

  const selectedOptions = useMemo(
    () =>
      value
        .map((id) => optionMap.get(id))
        .filter((opt): opt is MultiSelectOption => !!opt),
    [optionMap, value]
  )

  const availableOptions = useMemo(
    () => options.filter((opt) => !value.includes(opt.value)),
    [options, value]
  )

  const updateOpen = useCallback(
    (nextOpen: boolean) => {
      setOpen(nextOpen)
      onOpenChange?.(nextOpen)
    },
    [onOpenChange]
  )

  const updateDropdownPosition = useCallback(() => {
    const trigger = triggerRef.current
    if (!trigger) return

    const rect = trigger.getBoundingClientRect()
    setDropdownPosition({
      top: rect.bottom + 4,
      left: rect.left,
      width: rect.width,
    })
  }, [])

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
        (target instanceof Element && target.closest('[data-multiselect-dropdown]'))
      ) {
        return
      }
      updateOpen(false)
    }

    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [open, updateOpen])

  const addOption = (optionValue: number) => {
    if (disabled || value.includes(optionValue)) return
    onChange([...value, optionValue])
    updateOpen(false)
  }

  const removeOption = (optionValue: number) => {
    if (disabled) return
    onChange(value.filter((id) => id !== optionValue))
  }

  const clearAll = () => {
    if (disabled) return
    onChange([])
  }

  const handleKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
    if (disabled) return
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault()
      updateOpen(!open)
    }
    if (event.key === 'Escape') {
      updateOpen(false)
    }
  }

  const dropdown =
    open && !disabled && dropdownPosition
      ? createPortal(
          <div
            data-multiselect-dropdown
            style={{
              position: 'fixed',
              top: dropdownPosition.top,
              left: dropdownPosition.left,
              width: dropdownPosition.width,
            }}
            className="z-[9999] max-h-56 overflow-y-auto rounded-card border border-gray-200 bg-white py-1 shadow-lg"
          >
            {availableOptions.length === 0 ? (
              <p className="px-3 py-2 text-sm text-gray-500">No more options</p>
            ) : (
              availableOptions.map((option) => (
                <button
                  key={option.value}
                  type="button"
                  onClick={() => addOption(option.value)}
                  className="block w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700"
                >
                  {option.label}
                </button>
              ))
            )}
          </div>,
          document.body
        )
      : null

  return (
    <div ref={containerRef} className={cn('relative w-full', className)}>
      <div
        ref={triggerRef}
        role="combobox"
        aria-expanded={open}
        aria-disabled={disabled}
        tabIndex={disabled ? -1 : 0}
        onKeyDown={handleKeyDown}
        onClick={() => {
          if (!disabled) updateOpen(!open)
        }}
        className={cn(
          'flex min-h-11 w-full items-center gap-2 rounded-card border border-gray-300 bg-white px-3 py-2 text-sm transition',
          disabled
            ? 'cursor-default opacity-60'
            : 'cursor-text hover:border-gray-400',
          open && 'border-primary-400 ring-2 ring-primary-100'
        )}
      >
        <div className="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
          {selectedOptions.length === 0 ? (
            <span className="text-gray-400">{placeholder}</span>
          ) : (
            selectedOptions.map((option) => (
              <span
                key={option.value}
                className="inline-flex max-w-full items-center gap-1 rounded border border-primary-300 bg-primary-50 px-2 py-0.5 text-xs text-primary-700"
              >
                <button
                  type="button"
                  onClick={(event) => {
                    event.stopPropagation()
                    removeOption(option.value)
                  }}
                  disabled={disabled}
                  className="text-primary-600 hover:text-primary-800 disabled:pointer-events-none"
                  aria-label={`Remove ${option.label}`}
                >
                  <X size={12} />
                </button>
                <span className="truncate">{option.label}</span>
              </span>
            ))
          )}
        </div>

        <div className="flex shrink-0 items-center gap-1">
          {selectedOptions.length > 0 && !disabled && (
            <button
              type="button"
              onClick={(event) => {
                event.stopPropagation()
                clearAll()
              }}
              className="rounded p-0.5 text-gray-400 hover:text-gray-600"
              aria-label="Clear all selections"
            >
              <X size={14} />
            </button>
          )}
          <ChevronDown
            size={16}
            className={cn(
              'text-gray-400 transition-transform',
              open && 'rotate-180'
            )}
          />
        </div>
      </div>

      {dropdown}
    </div>
  )
}
