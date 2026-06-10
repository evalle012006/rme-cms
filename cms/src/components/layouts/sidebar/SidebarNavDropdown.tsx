import { useEffect, useState } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { ChevronDown, ChevronLeft, type LucideIcon } from 'lucide-react'
import { cn } from '@/lib/utils'
import type { NavChild } from '@/constants/sidebarNav'
import { SidebarNavFlyout } from '@/components/layouts/sidebar/SidebarNavFlyout'
import { sidebarItemClassName, sidebarSubItemClassName } from '@/components/layouts/sidebar/sidebarNavStyles'

interface SidebarNavDropdownProps {
  label: string
  icon: LucideIcon
  items: NavChild[]
  collapsed: boolean
}

export function SidebarNavDropdown({
  label,
  icon: Icon,
  items,
  collapsed,
}: SidebarNavDropdownProps) {
  const { pathname } = useLocation()
  const [open, setOpen] = useState(false)

  const isGroupActive = items.some(
    (item) => pathname === item.to || pathname.startsWith(`${item.to}/`)
  )

  useEffect(() => {
    if (isGroupActive) setOpen(true)
  }, [isGroupActive, pathname])

  const triggerClassName = sidebarItemClassName(isGroupActive, collapsed, 'between')

  const triggerContent = (
    <>
      <span
        className={cn(
          'flex items-center',
          collapsed ? 'justify-center' : 'gap-2.5'
        )}
      >
        <Icon
          size={collapsed ? 18 : 16}
          className={cn(
            'shrink-0 transition-colors',
            collapsed && isGroupActive && 'text-primary-300'
          )}
        />
        {!collapsed && <span className="truncate">{label}</span>}
      </span>
      {!collapsed && (open ? <ChevronDown size={14} /> : <ChevronLeft size={14} />)}
    </>
  )

  if (collapsed) {
    return (
      <SidebarNavFlyout label={label} collapsed items={items}>
        <div className={triggerClassName}>
          {triggerContent}
        </div>
      </SidebarNavFlyout>
    )
  }

  return (
    <div>
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className={triggerClassName}
      >
        {triggerContent}
      </button>
      {open && (
        <div className="ml-6 mt-0.5 space-y-0.5 border-l border-white/10 pl-2">
          {items.map((child) => (
            <NavLink
              key={child.to}
              to={child.to}
              className={({ isActive }) => sidebarSubItemClassName(isActive)}
            >
              {child.label}
            </NavLink>
          ))}
        </div>
      )}
    </div>
  )
}
