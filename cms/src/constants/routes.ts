// Centralized route definitions — single source of truth for all in-app paths.
//
// • Static paths are exposed as plain strings (e.g. ROUTES.users.list).
// • Parameterized paths expose BOTH:
//     - `<name>Pattern`  → use in <Route path={…}> definitions (e.g. "/users/edit/:id")
//     - `<name>(id)`     → use at call sites that need a real URL (e.g. navigate(...))
//
// Why a const object and not an `enum`?
//   – Better tree-shaking, preserves string literal types, no reverse-mapping
//     bloat, and stays compatible with `react-router`'s typed APIs.

export const ROUTES = {
  root: '/',
  login: '/login',
  dashboard: '/dashboard',

  settings: {
    profile: '/setting/profile',
  },

  users: {
    list: '/users',
    add: '/users/add',
    edit: (id: string | number) => `/users/edit/${id}`,
    editPattern: '/users/edit/:id',
  },

  builder: {
    approved: '/builder/approved',
    pending: '/builder/pending',
    add: '/builder/add',
    edit: (id: string | number) => `/builder/edit/${id}`,
    editPattern: '/builder/edit/:id',
  },

  rating: {
    settings: '/rating',
    add: '/rating/add',
    edit: (id: string | number) => `/rating/edit/${id}`,
    editPattern: '/rating/edit/:id',
  },

  company: {
    approved: '/company/approve',
    pending: '/company/pending',
    add: '/company/add',
    edit: (id: string | number) => `/company/edit/${id}`,
    editPattern: '/company/edit/:id',
  },

  reviews: {
    approved: '/review/approve',
    pending: '/review/pending',
    inactive: '/review/inactive',
    edit: (id: string | number) => `/review/edit/${id}`,
    editPattern: '/review/edit/:id',
  },

  properties: {
    approved: '/project/approve',
    pending: '/project/pending',
    add: '/project/add',
    edit: (id: string | number) => `/project/edit/${id}`,
    editPattern: '/project/edit/:id',
  },

  
  advertises: {
    approved: 'advertise/approve',
    pending: '/advertise/pending',
  },

  propertySettings: {
    categories: {
      list: '/project-category',
      add: '/project-category/add',
      edit: (id: string | number) => `/project-category/edit/${id}`,
      editPattern: '/project-category/edit/:id',
    },
    manageRating: '/project-category/manage-rating',
  },

  blogs: {
    categories: {
      list: '/blog-category',
      add: '/blog-category/add',
      edit: (id: string | number) => `/blog-category/edit/${id}`,
      editPattern: '/blog-category/edit/:id',
    },
    list: '/blogs',
    add: '/blogs/add',
    edit: (id: string | number) => `/blogs/edit/${id}`,
    editPattern: '/blogs/edit/:id',
    previewDraft: '/blogs/preview',
    preview: (id: string | number) => `/blogs/preview/${id}`,
    previewPattern: '/blogs/preview/:id',
  },


  cms: {
    home: '/cms/home',
    about: '/cms/about',
    propertyGuide: '/cms/property-guide',
    propertyCoach: '/cms/property-blog',
    contact: '/cms/contact',
    reviewsListing: '/cms/reviews-listing',
    reviewGuidelines: '/cms/review-guidelines',
    termsOfUse: '/cms/terms-of-use',
    privacyPolicy: '/cms/privacy-policy',
  },

  tags: '/tags',
  contactUs: '/contact',

 
} as const

// All static (non-parameterized) CMS paths — useful for guards/filters
// in route registration. Derived from ROUTES.cms so it can never drift.
export const CMS_PATHS = Object.values(ROUTES.cms)
