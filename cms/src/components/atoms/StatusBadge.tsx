import { cn } from '@/lib/utils'

export type StatusTone = 'approved' | 'pending'

export interface StatusBadgeProps {
  label: string
  tone: StatusTone
  onClick?: () => void
  disabled?: boolean
}

export function StatusBadge({ label, tone, onClick, disabled }: StatusBadgeProps) {
  const toneClasses =
    tone === 'approved'
      ? 'bg-emerald-500 hover:bg-emerald-600'
      : 'bg-red-500 hover:bg-red-600'

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
        tone === 'approved'
          ? 'focus-visible:ring-emerald-400'
          : 'focus-visible:ring-red-400',
        disabled && 'cursor-not-allowed opacity-60 hover:bg-current'
      )}
    >
      {label}
    </button>
  )
}
