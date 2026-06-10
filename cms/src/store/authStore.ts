import { create } from 'zustand'
import { persist } from 'zustand/middleware'

// ── Domain types ─────────────────────────────────────────────────────────────
export interface AuthUserRole {
  id: number
  name: string
  guard_name: string
}

export interface AuthUser {
  id: number
  name: string
  firstname: string
  lastname: string
  display_name: string | null
  mobile: string | null
  email: string
  profile_image: string | null
  profile_thumb_image: string | null
  location: string | null
  status: string
  full_name: string
  roles: AuthUserRole[]
}

// ── State & actions ──────────────────────────────────────────────────────────
interface AuthState {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean

  login: (user: AuthUser, token: string) => void
  logout: () => void
  updateUser: (user: AuthUser) => void
}

// ── Store ────────────────────────────────────────────────────────────────────
// NOTE: When using `persist` with TypeScript, the generic must be provided
// explicitly on `create<AuthState>()` — otherwise TypeScript can't infer the
// full state shape through the middleware wrapper.
export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,

      login: (user, token) => set({ user, token, isAuthenticated: true }),

      logout: () => set({ user: null, token: null, isAuthenticated: false }),

      updateUser: (user) => set({ user }),
    }),
    {
      name: 'rme-auth', // localStorage key
    }
  )
)