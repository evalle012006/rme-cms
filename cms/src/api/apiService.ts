import axios, {
  type AxiosError,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios'
import { toast } from 'sonner'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'

// ── Standard backend envelope ────────────────────────────────────────────────
// Every non-DataTables endpoint returns:
//   { status: "success" | "error", message?: string, data: ... }
export interface ApiEnvelope<T> {
  status: 'success' | 'error'
  code?: number
  message?: string
  data: T
}

export interface ApiRequestOptions {
  /** Toast on error. Default: true. Set false to handle the error manually. */
  showErrorToast?: boolean
  /** Fallback message when the server didn't provide one. */
  customErrorMessage?: string
  /** When set, a success toast with this text is shown on a successful call. */
  successMessage?: string
}

const DEFAULT_ERROR_MESSAGE = 'Something went wrong'

interface AxiosErrorBody {
  message?: string
  errors?: Record<string, string[]> // 422 validation errors
}

// ── Axios instance ───────────────────────────────────────────────────────────
export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// ── Request interceptor: attach bearer token ─────────────────────────────────
apiClient.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = useAuthStore.getState().token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Response interceptor: global HTTP error handling ─────────────────────────
// Strategy:
//   401 → clear auth + hard redirect to login (token expired/revoked)
//   422 → reject silently; the calling form renders inline validation errors
//   everything else → global Sonner toast + reject
//
// `isRedirecting` prevents a double-redirect when multiple concurrent
// requests all return 401 at the same time.
let isRedirecting = false

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<AxiosErrorBody>) => {
    const status = error.response?.status

    if (status === 401) {
      if (!isRedirecting) {
        isRedirecting = true
        useAuthStore.getState().logout()
        // Hard redirect clears all in-flight state cleanly. React Router's
        // navigate() isn't available outside components, so window.location
        // is the right tool here.
        window.location.href = ROUTES.login
      }
      return Promise.reject(error)
    }

    if (status === 422 || hasFieldErrors(error.response?.data)) {
      // Let the form component render field-level validation errors.
      return Promise.reject(error)
    }

    // 5xx, network failure, CORS, etc. — surface a single global toast.
    const message =
      error.response?.data?.message ??
      error.message ??
      'An unexpected error occurred. Please try again.'

    toast.error(message)

    return Promise.reject(error)
  }
)

// ── apiRequest: envelope unwrapper ───────────────────────────────────────────
// HTTP errors are toasted by the response interceptor above. This wrapper
// adds the missing piece: business-level errors that arrive as HTTP 200 with
// `status: "error"` in the body. We deliberately do NOT re-toast axios errors
// here — that would duplicate the interceptor's toast.

/**
 * Wraps an axios call that returns the standard envelope and resolves with the
 * unwrapped `data`. Handles toast + throw when `status === "error"`.
 *
 * @example
 *   const blog = await apiRequest(apiClient.get<ApiEnvelope<Blog>>(`/blog-get/${id}`))
 *
 * @example
 *   await apiRequest(
 *     apiClient.post<ApiEnvelope<Blog>>('/blog-add', payload),
 *     { successMessage: 'Blog created', customErrorMessage: 'Could not create blog' }
 *   )
 */
export async function apiRequest<T>(
  request: Promise<AxiosResponse<ApiEnvelope<T>>>,
  options: ApiRequestOptions = {}
): Promise<T> {
  const {
    showErrorToast = true,
    customErrorMessage,
    successMessage,
  } = options

  let response: AxiosResponse<ApiEnvelope<T>>

  try {
    response = await request
  } catch (error) {
    // HTTP failure path. The interceptor already toasts every non-401/422
    // error, so we just rethrow. Callers that need inline handling
    // (e.g. 422 form validation) can still catch and inspect.
    if (showErrorToast && customErrorMessage && axios.isAxiosError(error)) {
      const status = error.response?.status
      // For statuses the interceptor swallows silently (401/422), respect
      // that contract and stay silent. For anything else the interceptor
      // already showed a toast; surfacing `customErrorMessage` here would
      // double-toast. Use try/catch at the call-site if you need to override.
      void status
    }
    throw error
  }

  const body = response.data

  if (body && body.status === 'error') {
    const message =
      body.message || customErrorMessage || DEFAULT_ERROR_MESSAGE
    if (showErrorToast) toast.error(message)
    throw new ApiBusinessError(message, body)
  }

  if (successMessage) toast.success(successMessage)
  return body.data
}

/**
 * Variant for endpoints that don't follow the envelope (e.g. DataTables).
 * Returns `response.data` as-is. HTTP errors are still handled by the
 * interceptor; this just trims the axios wrapper for ergonomic consumption.
 */
export async function apiRequestRaw<T>(
  request: Promise<AxiosResponse<T>>
): Promise<T> {
  const response = await request
  return response.data
}

/** Thrown when the server responds 200 with `status: "error"` in the body. */
export class ApiBusinessError<T = unknown> extends Error {
  readonly envelope: ApiEnvelope<T>

  constructor(message: string, envelope: ApiEnvelope<T>) {
    super(message)
    this.name = 'ApiBusinessError'
    this.envelope = envelope
  }
}

/** True when the response body includes Laravel-style field errors. */
function hasFieldErrors(data: AxiosErrorBody | undefined): boolean {
  if (!data?.errors) return false
  return Object.keys(data.errors).length > 0
}

/** Type guard for axios errors with inline field validation messages. */
export function isValidationError(
  error: unknown
): error is AxiosError<AxiosErrorBody> & {
  response: { data: { errors: Record<string, string[]> } }
} {
  if (!axios.isAxiosError(error)) return false
  const err = error as AxiosError<AxiosErrorBody>
  return hasFieldErrors(err.response?.data)
}
