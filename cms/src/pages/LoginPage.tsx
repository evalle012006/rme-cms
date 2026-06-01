import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { toast } from 'sonner'
import { type AxiosError } from 'axios'
import { useAuthStore } from '@/store/authStore'
import { type AuthUser } from '@/store/authStore'
import api from '@/lib/api'

// ── Form schema ───────────────────────────────────────────────────────────────
const schema = z.object({
  email: z.string().email('Enter a valid email'),
  password: z.string().min(1, 'Password is required'),
})

type LoginFormData = z.infer<typeof schema>

// ── Laravel login response shape ──────────────────────────────────────────────
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
      toast.error(axiosError.response?.data?.message ?? 'Invalid credentials')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="bg-white rounded-2xl shadow-2xl p-8">
      <h2 className="text-lg font-semibold text-gray-900 mb-1">Sign in</h2>
      <p className="text-sm text-gray-500 mb-6">Enter your credentials to continue</p>

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
  )
}