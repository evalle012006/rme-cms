import { create } from 'zustand'
import { persist } from 'zustand/middleware'

// ── Domain types ─────────────────────────────────────────────────────────────
// Mirrors the user object returned by Laravel's /api/admin/login.
// Extend fields here as you consume more of the user payload.
export interface AuthUser {
  id: number
  name: string
  email: string
  // Add role, avatar, etc. as needed
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