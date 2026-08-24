import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Button } from '@/components/atoms/Button'
import { DescriptionText } from '@/components/atoms/DescriptionText'
import { HeaderText } from '@/components/atoms/HeaderText'
import { FormControl } from '@/components/atoms/FormControl'
import { useAuthStore } from '@/store/authStore'
import { authAPI } from '@/api/auth/authAPIHandler'
import { ROUTES } from '@/constants/routes'

const schema = z.object({
  email: z.string().email('Enter a valid email'),
  password: z.string().min(1, 'Password is required'),
})

type LoginFormData = z.infer<typeof schema>

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
      const { access_token, user } = await authAPI.login(formData, {
        customErrorMessage: 'Invalid credentials',
      })
      login(user, access_token)
      navigate(ROUTES.dashboard)
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="rounded-2xl bg-white p-8 shadow-2xl">
      <HeaderText className="mb-1">Login</HeaderText>
      <DescriptionText className="mb-6">
        Enter your credentials to continue
      </DescriptionText>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
        <FormControl
          label="Email address"
          type="email"
          autoComplete="email"
          placeholder="admin@gmail.com"
          error={errors.email?.message}
          {...register('email')}
        />

        <FormControl
          variant="password"
          label="Password"
          autoComplete="current-password"
          placeholder="••••••••"
          error={errors.password?.message}
          {...register('password')}
        />

        <Button
          size="lg"
          type="submit"
          color="primary"
          disabled={loading}
          className="h-auto w-full py-3.5 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {loading ? 'Signing in…' : 'Sign in'}
        </Button>
      </form>
    </div>
  )
}
