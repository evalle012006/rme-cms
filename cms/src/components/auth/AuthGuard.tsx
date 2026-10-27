import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'

export default function AuthGuard() {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)
  const location = useLocation()

  if (!isAuthenticated) {
    const redirect = `${location.pathname}${location.search}`
    const params = new URLSearchParams()
    if (redirect && redirect !== ROUTES.login) {
      params.set('redirect', redirect)
    }
    const to = params.toString() ? `${ROUTES.login}?${params.toString()}` : ROUTES.login
    return <Navigate to={to} replace />
  }

  return <Outlet />
}
