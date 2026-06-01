import { useState, useRef, useEffect } from 'react'
import { useNavigate, NavLink } from 'react-router-dom'
import { User, LogOut, Settings, ChevronDown } from 'lucide-react'
import { useAuthStore } from '@/store/authStore'
import { cn } from '@/lib/utils'

export default function TopBar() {
  const { user, logout } = useAuthStore()
  const navigate = useNavigate()
  const [open, setOpen] = useState(false)
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

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  // Derive display name — fall back to email prefix if name not set
  const displayName = user?.name ?? user?.email?.split('@')[0] ?? 'Admin'

  return (
    <header className="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-6 fixed top-0 right-0 left-60 z-30">
      {/* Left — page context could go here in future (breadcrumbs etc.) */}
      <div />

      {/* Right — user menu */}
      <div className="relative" ref={ref}>
        <button
          onClick={() => setOpen(!open)}
          className="flex items-center gap-2.5 text-sm text-gray-700 hover:text-gray-900 transition-colors"
        >
          {/* Avatar circle */}
          <span className="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-semibold uppercase">
            {displayName.charAt(0)}
          </span>
          <span className="font-medium hidden sm:block">{displayName}</span>
          <ChevronDown
            size={14}
            className={cn('text-gray-400 transition-transform', open && 'rotate-180')}
          />
        </button>

        {/* Dropdown */}
        {open && (
          <div className="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-lg py-1 z-50">
            <div className="px-4 py-2.5 border-b border-gray-100">
              <p className="text-xs font-medium text-gray-900 truncate">{displayName}</p>
              <p className="text-xs text-gray-400 truncate mt-0.5">{user?.email}</p>
            </div>

            <NavLink
              to="/settings/profile"
              onClick={() => setOpen(false)}
              className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
            >
              <Settings size={14} />
              Profile Settings
            </NavLink>

            <NavLink
              to="/settings/profile"
              onClick={() => setOpen(false)}
              className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
            >
              <User size={14} />
              My Account
            </NavLink>

            <div className="border-t border-gray-100 mt-1 pt-1">
              <button
                onClick={handleLogout}
                className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
              >
                <LogOut size={14} />
                Logout
              </button>
            </div>
          </div>
        )}
      </div>
    </header>
  )
}