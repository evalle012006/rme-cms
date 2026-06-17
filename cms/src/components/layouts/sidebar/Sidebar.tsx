import { useState } from 'react'
import { NavLink, useNavigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { Settings, LogOut } from 'lucide-react'
import { cn } from '@/lib/utils'
import { ROUTES } from '@/constants/routes'
import { NAV_ITEMS } from '@/constants/sidebarNav'
import { SidebarNavLink } from '@/components/layouts/sidebar/SidebarNavLink'
import { SidebarNavDropdown } from '@/components/layouts/sidebar/SidebarNavDropdown'
import { SidebarCollapseToggle } from '@/components/layouts/sidebar/SidebarCollapseToggle'
import { SidebarNavFlyout } from '@/components/layouts/sidebar/SidebarNavFlyout'
import { LogoutConfirmDialog } from '@/components/auth/LogoutConfirmDialog'

interface SidebarProps {
  open: boolean
  collapsed: boolean
  onCollapsedChange: (collapsed: boolean) => void
}

export default function Sidebar({ open, collapsed, onCollapsedChange }: SidebarProps) {
  const { logout } = useAuthStore()
  const navigate = useNavigate()
  const [logoutOpen, setLogoutOpen] = useState(false)

  const handleLogoutConfirm = () => {
    logout()
    navigate(ROUTES.login)
  }

  return (
    <aside
      className={cn(
        'bg-[#2f353a] flex flex-col fixed left-0 top-14 bottom-0 z-40',
        'transition-[width,transform] duration-300 ease-in-out',
        collapsed ? 'w-16' : 'w-52',
        !open && '-translate-x-full'
      )}
    >
      <nav
        className={cn(
          'flex-1 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden scrollbar-hide',
          collapsed ? 'px-2' : 'px-3'
        )}
      >
        {NAV_ITEMS.map((item) =>
          item.children ? (
            <SidebarNavDropdown
              key={item.label}
              label={item.label}
              icon={item.icon}
              items={item.children}
              collapsed={collapsed}
            />
          ) : (
            <SidebarNavLink
              key={item.label}
              label={item.label}
              icon={item.icon}
              to={item.to!}
              collapsed={collapsed}
            />
          )
        )}
      </nav>

      <div
        className={cn(
          'border-t border-white/10 shrink-0',
          collapsed ? 'px-2 py-3 space-y-1' : 'px-3 py-4'
        )}
      >
        <SidebarNavFlyout label="Profile Settings" collapsed={collapsed}>
          <NavLink
            to={ROUTES.settings.profile}
            className={cn(
              'flex items-center rounded-lg text-sm text-gray-300',
              'hover:bg-white/10 hover:text-white transition-colors',
              collapsed ? 'justify-center p-2.5' : 'gap-2.5 px-3 py-2'
            )}
          >
            <Settings size={collapsed ? 18 : 16} className="shrink-0" />
            {!collapsed && 'Profile Settings'}
          </NavLink>
        </SidebarNavFlyout>

        <SidebarNavFlyout label="Logout" collapsed={collapsed}>
          <button
            type="button"
            onClick={() => setLogoutOpen(true)}
            className={cn(
              'w-full flex items-center rounded-lg text-sm text-gray-400',
              'hover:bg-red-500/10 hover:text-red-400 transition-colors',
              collapsed ? 'justify-center p-2.5' : 'gap-2.5 px-3 py-2'
            )}
          >
            <LogOut size={collapsed ? 18 : 16} className="shrink-0" />
            {!collapsed && 'Logout'}
          </button>
        </SidebarNavFlyout>

        {/* {!collapsed && (
          <div className="mt-3 px-3">
            <p className="text-xs text-gray-500 truncate">{user?.email}</p>
          </div>
        )} */}
      </div>

      <SidebarCollapseToggle
        collapsed={collapsed}
        onToggle={() => onCollapsedChange(!collapsed)}
      />

      <LogoutConfirmDialog
        open={logoutOpen}
        onOpenChange={setLogoutOpen}
        onConfirm={handleLogoutConfirm}
      />
    </aside>
  )
}
