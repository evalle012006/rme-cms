import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { REVIEWS_LISTING_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Reviews Listing' },
]

export default function ReviewsListingPage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Reviews Listing page"
      config={REVIEWS_LISTING_CMS_CONFIG}
    />
  )
}
