import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import AuthGuard from '@/components/AuthGuard'
import DefaultLayout from '@/components/DefaultLayout'
import LoginPage from '@/pages/LoginPage'
import DashboardPage from '@/pages/DashboardPage'

// All other pages will be added here as they are built
// Example: import UsersPage from '@/pages/users/UsersPage'

export default function App() {
  return (
    <BrowserRouter>
      <Toaster position="top-right" toastOptions={{ duration: 3000 }} />
      <Routes>
        {/* Public routes */}
        <Route path="/login" element={<LoginPage />} />

        {/* Protected routes */}
        <Route element={<AuthGuard />}>
          <Route element={<DefaultLayout />}>
            <Route path="/dashboard" element={<DashboardPage />} />

            {/* Add routes here as pages are built */}
            {/* <Route path="/users" element={<UsersPage />} /> */}
            {/* <Route path="/users/add" element={<UserFormPage />} /> */}
            {/* <Route path="/users/edit/:id" element={<UserFormPage />} /> */}

            {/* Redirect root to dashboard */}
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Route>
        </Route>

        {/* 404 fallback */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
