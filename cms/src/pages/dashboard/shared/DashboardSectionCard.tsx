import type { ReactNode } from 'react'
import { cn } from '@/lib/utils'

type DashboardSectionCardProps = {
  title: string
  headerRight?: ReactNode
  children: ReactNode
  className?: string
}

export function DashboardSectionCard({
  title,
  headerRight,
  children,
  className,
}: DashboardSectionCardProps) {
  return (
    <div
      className={cn(
        'app-border overflow-hidden rounded-card bg-white text-gray-900 shadow-sm',
        className
      )}
    >
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3">
        <h2 className="text-sm font-semibold text-gray-700">{title}</h2>
        {headerRight}
      </div>
      {children}
    </div>
  )
}
