export const REVIEWS_ENDPOINTS = {
  DATATABLE:     '/datatable-reviews',
  GET_BY_ID:     '/review/get/:id',
  UPDATE:        '/review-update/:id',
  CHANGE_STATUS: '/review/change-status',
  DELETE:        '/review/delete/:id',
  BUYER_TYPES:   '/buyer-type-get',
  STATES:        '/get-states',
  SUBURBS:       '/get-suburbs',
} as const