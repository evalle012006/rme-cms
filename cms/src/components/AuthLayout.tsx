import { Outlet, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'

// AuthLayout wraps all unauthenticated routes (login, forgot-password, reset-password).
// If the user is already authenticated, redirect them straight to the dashboard
// so they never land on the login page while logged in.
export default function AuthLayout() {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />
  }

  return (
    <div className="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-4">
      {/* Branding — shared across all auth pages */}
      <div className="text-center mb-8">
        <h1 className="font-display text-white text-3xl font-bold tracking-tight">
          Rate<span className="text-primary-400">My</span>Estate
        </h1>
        <p className="text-gray-400 text-sm mt-2">Admin Panel</p>
      </div>

      {/* Page content (LoginPage, ForgotPasswordPage, etc.) */}
      <div className="w-full max-w-md">
        <Outlet />
      </div>
    </div>
  )
}