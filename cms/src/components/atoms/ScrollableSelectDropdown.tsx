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
}

export function ScrollableSelectDropdown({
  options,
  value,
  onChange,
  placeholder = 'Select…',
  disabled = false,
  className,
}: ScrollableSelectDropdownProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const triggerRef = useRef<HTMLButtonElement>(null)
  const [open, setOpen] = useState(false)
  const [dropdownPosition, setDropdownPosition] =
    useState<DropdownPosition | null>(null)

  const selectedLabel = useMemo(() => {
    if (!value) return null
    return options.find((option) => option.value === value)?.label ?? null
  }, [options, value])

  const updateDropdownPosition = useCallback(() => {
    const trigger = triggerRef.current
    if (!trigger) return

    const rect = trigger.getBoundingClientRect()
    const gap = 4
    const preferredMax = 224
    const spaceBelow = window.innerHeight - rect.bottom - 8
    const spaceAbove = rect.top - 8
    const openUp = spaceBelow < 160 && spaceAbove > spaceBelow
    const available = openUp ? spaceAbove : spaceBelow
    const maxHeight = Math.max(120, Math.min(preferredMax, available))

    setDropdownPosition({
      top: openUp ? rect.top - maxHeight - gap : rect.bottom + gap,
      left: rect.left,
      width: rect.width,
      maxHeight,
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
    if (disabled) setOpen(false)
  }, [disabled])

  const handleKeyDown = (event: KeyboardEvent<HTMLButtonElement>) => {
    if (disabled) return
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault()
      setOpen((prev) => !prev)
    }
    if (event.key === 'Escape') {
      setOpen(false)
    }
  }

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
            className="z-[9999] overflow-y-auto rounded-card border border-gray-200 bg-white py-1 shadow-lg"
          >
            <button
              type="button"
              onClick={() => {
                onChange('')
                setOpen(false)
              }}
              className={cn(
                'block w-full px-3 py-2 text-left text-sm hover:bg-primary-50 hover:text-primary-700',
                !value ? 'bg-primary-50 text-primary-700' : 'text-gray-700'
              )}
            >
              {placeholder}
            </button>
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => {
                  onChange(option.value)
                  setOpen(false)
                }}
                className={cn(
                  'block w-full px-3 py-2 text-left text-sm hover:bg-primary-50 hover:text-primary-700',
                  value === option.value
                    ? 'bg-primary-50 text-primary-700'
                    : 'text-gray-700'
                )}
              >
                {option.label}
              </button>
            ))}
          </div>,
          document.body
        )
      : null

  return (
    <div ref={containerRef} className="relative w-full">
      <button
        ref={triggerRef}
        type="button"
        disabled={disabled}
        aria-expanded={open}
        aria-haspopup="listbox"
        onClick={() => {
          if (!disabled) setOpen((prev) => !prev)
        }}
        onKeyDown={handleKeyDown}
        className={cn(
          'flex w-full items-center justify-between rounded-card border border-gray-300 bg-white py-2.5 pr-3 pl-3.5 text-left text-sm transition',
          'focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none',
          'disabled:pointer-events-none disabled:opacity-50',
          className
        )}
      >
        <span className={cn('truncate', !selectedLabel && 'text-gray-400')}>
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
