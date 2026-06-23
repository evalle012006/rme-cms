import { apiClient, apiRequestRaw } from '@/api/apiService'
import { UPLOAD_ENDPOINTS } from './uploadAPIEndpoints'

export interface UploadImageResponse {
  filename: string
  size: number
}

export const uploadAPI = {
  uploadImage: async (file: File): Promise<string> => {
    const fd = new FormData()
    fd.append('file', file)

    const data = await apiRequestRaw<UploadImageResponse>(
      apiClient.post<UploadImageResponse>(
        UPLOAD_ENDPOINTS.UPLOAD_IMAGES,
        fd,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      )
    )

    if (!data.filename) {
      throw new Error('No image path returned from upload')
    }

    return data.filename
  },
}
