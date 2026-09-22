export const REVIEWS_ENDPOINTS = {
  DATATABLE: '/datatable-reviews',
  STATUS_UPDATE: '/review/change-status',
  DELETE: (id: number) => `/review/delete/${id}`,
  GET: (id: number) => `/review/get/${id}`,
  UPDATE: (id: number) => `/review-update/${id}`,
  // Returns the buyer-type options + review categories used by the edit form.
  FORM_META: '/buyer-type-get',
  // CSV of reviews whose stored location could not be resolved to a locality.
  LOCALITY_EXCEPTIONS: '/reviews/locality-exceptions',
} as const
