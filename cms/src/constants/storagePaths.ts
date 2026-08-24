/** S3/local folder prefixes — must match backend BaseController + BlogCategory. */
export const STORAGE_PATHS = {
  PROFILE: 'profile-image/',
  FEATURE: 'feature-image/',
  COMPANY_LOGO: 'company-logo/',
  PROPERTY_LOGO: 'property/',
  PROPERTY_CAT: 'propertycat-icon/',
  BLOG_CATEGORY: 'blog-category-image/',
  CMS_PAGE: 'cms-images/',
  ADV: 'adv-images/',
  RATING_CONF: 'ratingconf-icon/',
} as const

export type StoragePath =
  (typeof STORAGE_PATHS)[keyof typeof STORAGE_PATHS]
