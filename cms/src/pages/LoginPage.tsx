import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { toast } from 'sonner'
import { useAuthStore } from '@/store/authStore'
import { type AuthUser } from '@/store/authStore'
import api from '@/lib/api'
import { type AxiosError } from 'axios'

// ── Form schema ───────────────────────────────────────────────────────────────
const schema = z.object({
  email: z.string().email('Enter a valid email'),
  password: z.string().min(1, 'Password is required'),
})

type LoginFormData = z.infer<typeof schema>

// ── Laravel login response shape ─────────────────────────────────────────────
// The Laravel controller returns: { data: { access_token, user }, message, success }
// NOTE: the key is `access_token`, not `token` — aligning with the actual API.
interface LoginResponseData {
  data: {
    access_token: string
    user: AuthUser
  }
  message: string
  success: boolean
}

interface ApiErrorResponse {
  message?: string
}

// ── Component ─────────────────────────────────────────────────────────────────
export default function LoginPage() {
  const [loading, setLoading] = useState(false)
  const login = useAuthStore((s) => s.login)
  const navigate = useNavigate()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormData>({
    resolver: zodResolver(schema),
  })

  const onSubmit = async (formData: LoginFormData) => {
    setLoading(true)
    try {
      const res = await api.post<LoginResponseData>('/auth/login', formData)
      const { access_token, user } = res.data.data
      login(user, access_token)
      navigate('/dashboard')
    } catch (err) {
      const axiosError = err as AxiosError<ApiErrorResponse>
      // 401 is handled globally by the interceptor (redirect), but login 401s
      // should show an inline error instead. The interceptor won't redirect here
      // because the user is not yet authenticated — but to be safe, we surface
      // the message directly.
      toast.error(axiosError.response?.data?.message ?? 'Invalid credentials')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <h1 className="font-display text-white text-3xl font-bold tracking-tight">
            Rate<span className="text-primary-400">My</span>Estate
          </h1>
          <p className="text-gray-400 text-sm mt-2">Admin Panel — Sign in to continue</p>
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl shadow-2xl p-8">
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">
                Email address
              </label>
              <input
                {...register('email')}
                type="email"
                autoComplete="email"
                placeholder="admin@gmail.com"
                className="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
              />
              {errors.email && (
                <p className="text-red-500 text-xs mt-1">{errors.email.message}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">
                Password
              </label>
              <input
                {...register('password')}
                type="password"
                autoComplete="current-password"
                placeholder="••••••••"
                className="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
              />
              {errors.password && (
                <p className="text-red-500 text-xs mt-1">{errors.password.message}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-2.5 px-4 bg-primary-500 hover:bg-primary-600 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition"
            >
              {loading ? 'Signing in…' : 'Sign in'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}