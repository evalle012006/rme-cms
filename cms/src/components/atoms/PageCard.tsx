import { cn } from '@/lib/utils'
import type { ComponentProps, ReactNode } from 'react'

export type PageCardProps = ComponentProps<'div'> & {
  title?: string
  actions?: ReactNode
  children: ReactNode
  bodyClassName?: string
  /** Allow popovers (e.g. Quill link/video tooltips) to extend outside the card. */
  overflowVisible?: boolean
}

/** Bordered card shell used on CMS sections, profile, and entity forms. */
export function PageCard({
  title,
  actions,
  children,
  className,
  bodyClassName,
  overflowVisible = false,
  ...props
}: PageCardProps) {
  return (
    <div
      className={cn(
        'rounded-lg app-border bg-white shadow-sm',
        overflowVisible
          ? 'relative z-0 overflow-visible focus-within:z-50'
          : 'overflow-hidden',
        className
      )}
      {...props}
    >
      {title != null && (
        <div
          className={cn(
            'border-b border-gray-300 bg-gray-50 px-5 py-3',
            actions != null && 'flex items-center justify-between'
          )}
        >
          <h3 className="text-sm font-medium text-gray-700">{title}</h3>
          {actions}
        </div>
      )}
      <div className={cn('p-5', bodyClassName)}>{children}</div>
    </div>
  )
}
