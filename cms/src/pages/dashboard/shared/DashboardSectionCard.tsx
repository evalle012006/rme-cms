import type { ReactNode } from 'react'
import { cn } from '@/lib/utils'

type DashboardSectionCardProps = {
  title: string
  headerRight?: ReactNode
  exportSubtitle?: string
  children: ReactNode
  className?: string
  'data-dashboard-export'?: string
}

export function DashboardSectionCard({
  title,
  headerRight,
  exportSubtitle,
  children,
  className,
  'data-dashboard-export': dashboardExport,
}: DashboardSectionCardProps) {
  return (
    <div
      data-dashboard-export={dashboardExport}
      className={cn(
        'app-border overflow-hidden rounded-card bg-white text-gray-900 shadow-sm',
        className
      )}
    >
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3">
        <div>
          <h2 className="text-sm font-semibold text-gray-700">{title}</h2>
          {exportSubtitle ? (
            <p className="sr-only" data-dashboard-export-subtitle>
              {exportSubtitle}
            </p>
          ) : null}
        </div>
        {headerRight}
      </div>
      {children}
    </div>
  )
}
