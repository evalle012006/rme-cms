import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'sonner'
import AuthLayout from '@/components/AuthLayout'
import AuthGuard from '@/components/AuthGuard'
import DefaultLayout from '@/components/DefaultLayout'
import LoginPage from '@/pages/LoginPage'
import DashboardPage from '@/pages/DashboardPage'

// Add page imports here as they are built:
// import UsersPage from '@/pages/users/UsersPage'

export default function App() {
  return (
    <BrowserRouter>
      <Toaster position="top-right" richColors />

      <Routes>
        {/* Unauthenticated routes — centered card shell, redirects to /dashboard if already logged in */}
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
          {/* Future: <Route path="/forgot-password" element={<ForgotPasswordPage />} /> */}
          {/* Future: <Route path="/reset-password" element={<ResetPasswordPage />} /> */}
        </Route>

        {/* Authenticated routes — sidebar + top bar, redirects to /login if logged out */}
        <Route element={<AuthGuard />}>
          <Route element={<DefaultLayout />}>
            <Route path="/dashboard" element={<DashboardPage />} />

            {/* Add routes here as pages are built */}
            {/* <Route path="/users" element={<UsersPage />} /> */}

            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Route>
        </Route>

        {/* 404 fallback */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}