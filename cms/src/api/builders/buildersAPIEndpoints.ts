export const BUILDERS_ENDPOINTS = {
  DATATABLE: '/datatable-builders',
  CHANGE_STATUS: '/builder/change-builder-status',
  GET_BY_ID: '/builder/get-by-id',
  // Endpoint name preserves the backend's misspelling ("udpate" instead of
  // "update") — we don't control the API.
  UPDATE: '/builder/udpate-builder',
} as const
