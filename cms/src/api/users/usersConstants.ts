// ── User status (create endpoints) ───────────────────────────────────────────
// `/auth/reviewer/signup` and `/auth/builder/signup` expect a numeric status
// value in the form-data payload. We keep the values as numeric strings so
// they slot directly into the <select>'s string-only value space.
//
// Mapping is taken from the legacy admin and observed signup payloads:
//   "1" = Approved (confirmed via reviewer signup payload)
//   "2" = Pending  (1-indexed order of the dropdown options)
//   "3" = Rejected
//   "4" = Suspended

export const USER_STATUS = {
  APPROVED: "1",
  PENDING: "2",
  REJECTED: "3",
  SUSPENDED: "4",
} as const

export type UserStatus = (typeof USER_STATUS)[keyof typeof USER_STATUS]

export const USER_STATUS_OPTIONS: { value: UserStatus; label: string }[] = [
  { value: USER_STATUS.APPROVED, label: "Approved" },
  { value: USER_STATUS.PENDING, label: "Pending" },
  { value: USER_STATUS.REJECTED, label: "Rejected" },
  { value: USER_STATUS.SUSPENDED, label: "Suspended" },
]

// ── User numeric status (edit endpoints) ─────────────────────────────────────
// `/user` and `/auth/reviewer/update` use a *different* numeric encoding:
// a binary 1/0 for Approved/Pending. Kept separate from USER_STATUS so the
// two flows (create vs edit) don't get conflated.

export const USER_NUMERIC_STATUS = {
  APPROVED: "1",
  PENDING: "0",
} as const

export type UserNumericStatus =
  (typeof USER_NUMERIC_STATUS)[keyof typeof USER_NUMERIC_STATUS]

export const USER_NUMERIC_STATUS_OPTIONS: {
  value: UserNumericStatus
  label: string
}[] = [
  { value: USER_NUMERIC_STATUS.APPROVED, label: "Approved" },
  { value: USER_NUMERIC_STATUS.PENDING, label: "Pending" },
]

// ── Brand type (Builder / Developer users) ───────────────────────────────────
// `/auth/builder/signup` expects a numeric brand_type. Same numeric-string
// pattern as USER_STATUS so the value slots straight into a <select>.
//
//   "1" = Builder
//   "2" = Developer (confirmed via builder signup payload)
//   "3" = Builder & Developer

export const BRAND_TYPE = {
  BUILDER: "1",
  DEVELOPER: "2",
  BUILDER_AND_DEVELOPER: "3",
} as const

export type BrandType = (typeof BRAND_TYPE)[keyof typeof BRAND_TYPE]

export const BRAND_TYPE_OPTIONS: { value: BrandType; label: string }[] = [
  { value: BRAND_TYPE.BUILDER, label: "Builder" },
  { value: BRAND_TYPE.DEVELOPER, label: "Developer" },
  { value: BRAND_TYPE.BUILDER_AND_DEVELOPER, label: "Developer and Builder" },
]

// ── User add tab type ────────────────────────────────────────────────────────

export const USER_TAB = {
  REVIEWER: "reviewer",
  BUILDER_DEVELOPER: "builder_developer",
} as const

export type UserTab = (typeof USER_TAB)[keyof typeof USER_TAB]
