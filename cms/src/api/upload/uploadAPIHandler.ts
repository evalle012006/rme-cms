import { apiClient, apiRequestRaw } from '@/api/apiService'
import type { StoragePath } from '@/constants/storagePaths'
import { UPLOAD_ENDPOINTS } from './uploadAPIEndpoints'

export interface UploadImageResponse {
  filename: string
  size: number
}

export const uploadAPI = {
  uploadImage: async (file: File, imagePath: StoragePath): Promise<string> => {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('image_path', imagePath)

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
