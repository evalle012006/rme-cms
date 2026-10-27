import { Outlet, Navigate, useSearchParams } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'
import { safeRedirectPath } from '@/lib/safeRedirect'
import badge from '@/assets/badge.png'

// AuthLayout wraps all unauthenticated routes (login, forgot-password, reset-password).
// If the user is already authenticated, send them to the page they were trying
// to open (for example an email deep link) or the dashboard.
export default function AuthLayout() {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)
  const [searchParams] = useSearchParams()

  if (isAuthenticated) {
    const redirect = safeRedirectPath(searchParams.get('redirect')) ?? ROUTES.dashboard
    return <Navigate to={redirect} replace />
  }

  return (
    <div className="relative min-h-screen bg-surface flex flex-col items-center justify-center p-4">
      {/* Website badge — top left */}
      <div className="absolute top-5 left-6">
        <img src={badge} alt="RateMyEstate" className="h-10 w-auto" />
      </div>

      <div className="w-full max-w-md">
        <Outlet />
      </div>
    </div>
  )
}