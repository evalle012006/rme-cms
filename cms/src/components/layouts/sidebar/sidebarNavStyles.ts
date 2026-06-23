import { cn } from '@/lib/utils'

export function sidebarItemClassName(
  active: boolean,
  collapsed: boolean,
  expandedLayout?: 'default' | 'between'
) {
  return cn(
    'w-full flex items-center rounded-lg text-sm transition-colors',
    collapsed
      ? 'justify-center p-2.5'
      : expandedLayout === 'between'
        ? 'justify-between px-3 py-2'
        : 'gap-2.5 px-3 py-2',
    active
      ? collapsed
        ? 'bg-[#05B0AA]/35 text-primary-300 ring-1 ring-primary-400/60'
        : 'bg-[#05B0AA]/25 text-white font-medium shadow-sm ring-1 ring-inset ring-[#05B0AA]/50'
      : 'text-gray-300 hover:bg-[#05B0AA] hover:text-white'
  )
}

export function sidebarSubItemClassName(active: boolean) {
  return cn(
    'block rounded-md text-xs transition-colors border-l-2',
    active
      ? 'border-primary-400 bg-[#05B0AA]/20 text-white font-medium pl-2.5 pr-2 py-1.5'
      : 'border-transparent text-gray-400 hover:text-white hover:bg-white/5 pl-2.5 pr-2 py-1.5'
  )
}

export function sidebarFlyoutSubItemClassName(active: boolean) {
  return cn(
    'block px-4 py-2 text-sm transition-colors border-l-2',
    active
      ? 'border-primary-400 bg-[#05B0AA]/25 text-white font-medium'
      : 'border-transparent text-gray-400 hover:text-white hover:bg-white/5'
  )
}
