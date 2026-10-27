import { cn } from '@/lib/utils'

export type StatusTone = 'approved' | 'pending' | 'inactive'

export interface StatusBadgeProps {
  label: string
  tone: StatusTone
  onClick?: () => void
  disabled?: boolean
}

const TONE_CLASSES: Record<StatusTone, string> = {
  approved: 'bg-emerald-500 hover:bg-emerald-600',
  pending: 'bg-red-500 hover:bg-red-600',
  inactive: 'bg-gray-500 hover:bg-gray-600',
}

const RING_CLASSES: Record<StatusTone, string> = {
  approved: 'focus-visible:ring-emerald-400',
  pending: 'focus-visible:ring-red-400',
  inactive: 'focus-visible:ring-gray-400',
}

export function StatusBadge({ label, tone, onClick, disabled }: StatusBadgeProps) {
  const toneClasses = TONE_CLASSES[tone]

  const baseClasses =
    'inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold text-white shadow-sm'

  if (!onClick) {
    return <span className={cn(baseClasses, toneClasses)}>{label}</span>
  }

  return (
    <button
      type="button"
      onClick={onClick}
      disabled={disabled}
      className={cn(
        baseClasses,
        toneClasses,
        'cursor-pointer transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1',
        RING_CLASSES[tone],
        disabled && 'cursor-not-allowed opacity-60 hover:bg-current'
      )}
    >
      {label}
    </button>
  )
}
