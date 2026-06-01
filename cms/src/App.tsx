import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'sonner'
import AuthGuard from '@/components/AuthGuard'
import DefaultLayout from '@/components/DefaultLayout'
import LoginPage from '@/pages/LoginPage'
import DashboardPage from '@/pages/DashboardPage'

// Add page imports here as they are built:
// import UsersPage from '@/pages/users/UsersPage'

export default function App() {
  return (
    <BrowserRouter>
      {/* Sonner — replaces react-hot-toast; position matches original */}
      <Toaster position="top-right" richColors />

      <Routes>
        {/* Public */}
        <Route path="/login" element={<LoginPage />} />

        {/* Protected — wrapped by AuthGuard, rendered inside DefaultLayout */}
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