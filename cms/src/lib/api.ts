import axios, { type AxiosError, type InternalAxiosRequestConfig } from 'axios'
import { toast } from 'sonner'
import { useAuthStore } from '@/store/authStore'

// ── Laravel API error shape ──────────────────────────────────────────────────
// Laravel returns validation errors differently from general errors.
// 422 responses have an `errors` object; everything else uses `message`.
interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]> // 422 validation errors
}

// ── Axios instance ───────────────────────────────────────────────────────────
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// ── Request interceptor ──────────────────────────────────────────────────────
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = useAuthStore.getState().token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Response interceptor ─────────────────────────────────────────────────────
// Error handling strategy:
//   401 → clear auth, redirect to login (token expired or revoked)
//   422 → reject silently; the calling form handles inline validation errors
//   everything else → global Sonner toast + reject
//
// Guard flag prevents double-redirect when concurrent requests both 401.
let isRedirecting = false

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiErrorResponse>) => {
    const status = error.response?.status

    if (status === 401) {
      if (!isRedirecting) {
        isRedirecting = true
        useAuthStore.getState().logout()
        // Hard redirect — clears all in-flight state cleanly.
        // React Router navigate() is not available outside components,
        // so window.location is correct here.
        window.location.href = '/login'
      }
      return Promise.reject(error)
    }

    if (status === 422) {
      // Let the form component handle validation feedback.
      return Promise.reject(error)
    }

    // All other errors (500, 503, network failure, etc.)
    const message =
      error.response?.data?.message ??
      error.message ??
      'An unexpected error occurred. Please try again.'

    toast.error(message)

    return Promise.reject(error)
  }
)

export default api