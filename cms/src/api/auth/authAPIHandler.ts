import {
  apiClient,
  apiRequest,
  type ApiEnvelope,
  type ApiRequestOptions,
} from '@/api/apiService'
import { uploadAPI } from '@/api/upload/uploadAPIHandler'
import { AUTH_ENDPOINTS } from './authAPIEndpoints'
import type { AuthUser } from '@/store/authStore'

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginData {
  access_token: string
  user: AuthUser
}

export interface UpdateProfilePayload {
  firstname: string
  lastname: string
  email: string
  /** New upload as binary, or existing storage path when unchanged. */
  profile_image?: File | string | null
}

export const authAPI = {
  login: (
    payload: LoginPayload,
    options?: ApiRequestOptions
  ): Promise<LoginData> =>
    apiRequest<LoginData>(
      apiClient.post<ApiEnvelope<LoginData>>(AUTH_ENDPOINTS.LOGIN, payload),
      options
    ),

  logout: (options?: ApiRequestOptions): Promise<unknown> =>
    apiRequest<unknown>(
      apiClient.post<ApiEnvelope<unknown>>(AUTH_ENDPOINTS.LOGOUT),
      options
    ),

  updateProfile: async (payload: UpdateProfilePayload): Promise<string | null> => {
    const profileImagePath =
      payload.profile_image instanceof File
        ? await uploadAPI.uploadImage(payload.profile_image)
        : payload.profile_image ?? null

    const fd = new FormData()
    fd.append('firstname', payload.firstname)
    fd.append('lastname', payload.lastname)
    fd.append('email', payload.email)
    if (profileImagePath) {
      fd.append('profile_image', profileImagePath)
    }

    await apiClient.post<ApiEnvelope<unknown>>(AUTH_ENDPOINTS.USER_UPDATE_ME, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    return profileImagePath
  },

  requestPasswordReset: () =>
    apiClient.post<ApiEnvelope<unknown>>(AUTH_ENDPOINTS.PASSWORD_RESET_ME, {}),
}
