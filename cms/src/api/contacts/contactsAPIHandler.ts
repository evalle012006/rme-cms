import { apiClient, apiRequestRaw } from '@/api/apiService'
import { CONTACTS_ENDPOINTS } from './contactsAPIEndpoints'
import type { DataTablesRequest, DataTablesResponse } from '@/types/api'

export interface ContactDetail {
  id: number
  firstname: string | null
  lastname: string | null
  email: string
  subject: string | null
  message: string | null
  created_at: string
  updated_at: string
}

export const contactsAPI = {
  // DataTables endpoint uses its own response shape (no envelope).
  datatable: (
    payload: DataTablesRequest
  ): Promise<DataTablesResponse<ContactDetail>> =>
    apiRequestRaw(
      apiClient.post<DataTablesResponse<ContactDetail>>(
        CONTACTS_ENDPOINTS.DATATABLE,
        payload
      )
    ),
}
