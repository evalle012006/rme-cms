import { NavLink } from 'react-router-dom'
import { type LucideIcon } from 'lucide-react'
import { cn } from '@/lib/utils'
import { SidebarNavFlyout } from '@/components/layouts/sidebar/SidebarNavFlyout'
import { sidebarItemClassName } from '@/components/layouts/sidebar/sidebarNavStyles'

interface SidebarNavLinkProps {
  label: string
  icon: LucideIcon
  to: string
  collapsed: boolean
}

export function SidebarNavLink({ label, icon: Icon, to, collapsed }: SidebarNavLinkProps) {
  const link = (
    <NavLink
      to={to}
      className={({ isActive }) => sidebarItemClassName(isActive, collapsed)}
    >
      {({ isActive }) => (
        <>
          <Icon
            size={collapsed ? 18 : 16}
            className={cn(
              'shrink-0 transition-colors',
              collapsed && isActive && 'text-primary-300'
            )}
          />
          {!collapsed && <span className="truncate">{label}</span>}
        </>
      )}
    </NavLink>
  )

  return (
    <SidebarNavFlyout label={label} collapsed={collapsed}>
      {link}
    </SidebarNavFlyout>
  )
}
