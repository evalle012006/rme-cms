import { ChevronLeft, ChevronRight } from 'lucide-react'
import { cn } from '@/lib/utils'

interface SidebarCollapseToggleProps {
  collapsed: boolean
  onToggle: () => void
}

export function SidebarCollapseToggle({ collapsed, onToggle }: SidebarCollapseToggleProps) {
  return (
    <div className="shrink-0 border-t border-black/40 bg-[#1a1e21]">
      <button
        type="button"
        onClick={onToggle}
        aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
        className={cn(
          'flex w-full items-center py-3 text-gray-400',
          collapsed ? 'justify-center' : 'justify-end px-3',
          'transition-colors hover:bg-white/5 hover:text-white'
        )}
      >
        {collapsed ? (
          <ChevronRight size={22} strokeWidth={2} />
        ) : (
          <ChevronLeft size={22} strokeWidth={2} />
        )}
      </button>
    </div>
  )
}
