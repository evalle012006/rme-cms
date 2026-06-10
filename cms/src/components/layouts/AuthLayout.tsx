import { Outlet, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'
import badge from '@/assets/badge.png'

// AuthLayout wraps all unauthenticated routes (login, forgot-password, reset-password).
// If the user is already authenticated, redirect them straight to the dashboard
// so they never land on the login page while logged in.
export default function AuthLayout() {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)

  if (isAuthenticated) {
    return <Navigate to={ROUTES.dashboard} replace />
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