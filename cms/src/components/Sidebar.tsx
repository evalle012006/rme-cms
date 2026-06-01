import { useState } from 'react'
import { NavLink, useNavigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import {
  LayoutDashboard, Users, Building2, Star, Home,
  Tag, FileText, Mail, Settings, LogOut,
  ChevronDown, ChevronRight, Megaphone,
  type LucideIcon,
} from 'lucide-react'
import { cn } from '@/lib/utils'

// ── Nav config types ──────────────────────────────────────────────────────────
interface NavChild {
  label: string
  to: string
}

interface NavItemConfig {
  label: string
  icon: LucideIcon
  to?: string
  children?: NavChild[]
}

// ── Nav config ────────────────────────────────────────────────────────────────
const nav: NavItemConfig[] = [
  { label: 'Dashboard', icon: LayoutDashboard, to: '/dashboard' },
  {
    label: 'Users', icon: Users, children: [
      { label: 'All Users', to: '/users' },
    ],
  },
  {
    label: 'Company Users', icon: Building2, children: [
      { label: 'Approved', to: '/builders/approved' },
      { label: 'Pending', to: '/builders/pending' },
    ],
  },
  {
    label: 'Company', icon: Building2, children: [
      { label: 'Approved', to: '/companies/approved' },
      { label: 'Pending', to: '/companies/pending' },
    ],
  },
  {
    label: 'Reviews', icon: Star, children: [
      { label: 'Approved', to: '/reviews/approved' },
      { label: 'Pending', to: '/reviews/pending' },
    ],
  },
  {
    label: 'Properties', icon: Home, children: [
      { label: 'Approved', to: '/properties/approved' },
      { label: 'Pending', to: '/properties/pending' },
    ],
  },
  {
    label: 'Advertises', icon: Megaphone, children: [
      { label: 'Approved', to: '/advertises/approved' },
      { label: 'Pending', to: '/advertises/pending' },
    ],
  },
  {
    label: 'Property Settings', icon: Settings, children: [
      { label: 'Categories', to: '/property-categories' },
      { label: 'Manage Rating', to: '/manage-rating' },
    ],
  },
  {
    label: 'Blogs', icon: FileText, children: [
      { label: 'Blog Categories', to: '/blog-categories' },
      { label: 'All Blogs', to: '/blogs' },
    ],
  },
  {
    label: 'CMS Pages', icon: FileText, children: [
      { label: 'Home', to: '/cms/home' },
      { label: 'About', to: '/cms/about' },
      { label: 'Property Guide', to: '/cms/property-guide' },
      { label: 'Property Coach', to: '/cms/property-coach' },
      { label: 'Contact', to: '/cms/contact' },
      { label: 'Review Guidelines', to: '/cms/review-guidelines' },
      { label: 'Terms of Use', to: '/cms/terms-of-use' },
      { label: 'Privacy Policy', to: '/cms/privacy-policy' },
    ],
  },
  { label: 'Rating Setting', icon: Star, to: '/rating-setting' },
  { label: 'Manage Tags', icon: Tag, to: '/tags' },
  { label: 'Contact Us', icon: Mail, to: '/contact-us' },
]

// ── NavItem ───────────────────────────────────────────────────────────────────
function NavItem({ item }: { item: NavItemConfig }) {
  const [open, setOpen] = useState(false)

  if (item.children) {
    return (
      <div>
        <button
          onClick={() => setOpen(!open)}
          className="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors"
        >
          <span className="flex items-center gap-2.5">
            <item.icon size={16} />
            {item.label}
          </span>
          {open ? <ChevronDown size={14} /> : <ChevronRight size={14} />}
        </button>
        {open && (
          <div className="ml-6 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
            {item.children.map((child) => (
              <NavLink
                key={child.to}
                to={child.to}
                className={({ isActive }) =>
                  cn(
                    'block px-2 py-1.5 rounded text-xs transition-colors',
                    isActive
                      ? 'text-primary-300 font-medium'
                      : 'text-gray-400 hover:text-white'
                  )
                }
              >
                {child.label}
              </NavLink>
            ))}
          </div>
        )}
      </div>
    )
  }

  // Leaf node — `to` is guaranteed when there are no children
  return (
    <NavLink
      to={item.to!}
      className={({ isActive }) =>
        cn(
          'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors',
          isActive
            ? 'bg-primary-500/20 text-primary-300 font-medium'
            : 'text-gray-300 hover:bg-white/10 hover:text-white'
        )
      }
    >
      <item.icon size={16} />
      {item.label}
    </NavLink>
  )
}

// ── Sidebar ───────────────────────────────────────────────────────────────────
export default function Sidebar() {
  const { user, logout } = useAuthStore()
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  return (
    <aside className="w-60 min-h-screen bg-gray-900 flex flex-col fixed left-0 top-0 bottom-0 z-40">
      {/* Logo */}
      <div className="px-4 py-5 border-b border-white/10">
        <h1 className="font-display text-white text-lg font-bold tracking-tight">
          Rate<span className="text-primary-400">My</span>Estate
        </h1>
        <p className="text-gray-500 text-xs mt-0.5">Admin Panel</p>
      </div>

      {/* Nav */}
      <nav className="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto scrollbar-hide">
        {nav.map((item) => (
          <NavItem key={item.label} item={item} />
        ))}
      </nav>

      {/* User + Logout */}
      <div className="px-3 py-4 border-t border-white/10">
        <NavLink
          to="/settings/profile"
          className="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors mb-1"
        >
          <Settings size={16} />
          Profile Settings
        </NavLink>
        <button
          onClick={handleLogout}
          className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition-colors"
        >
          <LogOut size={16} />
          Logout
        </button>
        <div className="mt-3 px-3">
          <p className="text-xs text-gray-500 truncate">{user?.email}</p>
        </div>
      </div>
    </aside>
  )
}