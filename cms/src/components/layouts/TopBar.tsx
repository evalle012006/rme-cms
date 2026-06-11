import { useState, useRef, useEffect } from 'react'
import { useNavigate, NavLink } from 'react-router-dom'
import { LogOut, Settings, ChevronDown, Menu } from 'lucide-react'
import { useAuthStore } from '@/store/authStore'
import { cn } from '@/lib/utils'
import { ROUTES } from '@/constants/routes'
import { LogoutConfirmDialog } from '@/components/auth/LogoutConfirmDialog'
import navbarImg1 from '@/assets/navbarImg1.png'
import navbarImg2 from '@/assets/navbarImg2.png'

interface TopBarProps {
  sidebarOpen: boolean
  sidebarCollapsed: boolean
  onToggleSidebar: () => void
}

export default function TopBar({ sidebarOpen, sidebarCollapsed, onToggleSidebar }: TopBarProps) {
  const { user, logout } = useAuthStore()
  const navigate = useNavigate()
  const [open, setOpen] = useState(false)
  const [logoutOpen, setLogoutOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

  // Close dropdown when clicking outside
  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const handleLogoutClick = () => {
    setOpen(false)
    setLogoutOpen(true)
  }

  const handleLogoutConfirm = () => {
    logout()
    navigate(ROUTES.login)
  }

  // Derive display name — fall back to email prefix if name not set
  const displayName = user?.name ?? user?.email?.split('@')[0] ?? 'Admin'

  return (
    <header className="h-14 bg-white app-border-b flex items-center justify-between px-6 fixed top-0 left-0 right-0 z-50">
      <div className="flex items-center gap-3">
        <div className="flex items-center gap-2">
          <img src={navbarImg1} alt="" className="h-8 w-auto shrink-0" />
          {!sidebarCollapsed && (
            <img src={navbarImg2} alt="RateMyEstate" className="h-8 w-auto shrink-0" />
          )}
        </div>
        <button
          type="button"
          onClick={onToggleSidebar}
          aria-label={sidebarOpen ? 'Hide sidebar' : 'Show sidebar'}
          aria-expanded={sidebarOpen}
          className="p-1.5 rounded-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
        >
          <Menu size={20} strokeWidth={2} />
        </button>
      </div>

      <div className="relative" ref={ref}>
        <button
          onClick={() => setOpen(!open)}
          className="flex items-center gap-2.5 text-sm text-gray-700 hover:text-gray-900 transition-colors"
        >
          <span className="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-semibold uppercase">
            {displayName.charAt(0)}
          </span>
          <span className="font-medium hidden sm:block">{displayName}</span>
          <ChevronDown
            size={14}
            className={cn('text-gray-400 transition-transform', open && 'rotate-180')}
          />
        </button>

        {open && (
          <div className="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-lg py-1 z-50">
            <div className="px-4 py-2.5 border-b border-gray-100">
              <p className="text-xs font-medium text-gray-900 truncate">{displayName}</p>
              <p className="text-xs text-gray-400 truncate mt-0.5">{user?.email}</p>
            </div>

            <NavLink
              to={ROUTES.settings.profile}
              onClick={() => setOpen(false)}
              className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
            >
              <Settings size={14} />
              Profile Settings
            </NavLink>

            <div className="border-t border-gray-100 mt-1 pt-1">
              <button
                onClick={handleLogoutClick}
                className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
              >
                <LogOut size={14} />
                Logout
              </button>
            </div>
          </div>
        )}
      </div>

      <LogoutConfirmDialog
        open={logoutOpen}
        onOpenChange={setLogoutOpen}
        onConfirm={handleLogoutConfirm}
      />
    </header>
  )
}