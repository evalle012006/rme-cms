import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { REVIEW_GUIDELINES_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Review Guidelines' },
]

export default function ReviewGuidelinesPage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Review Guidelines page"
      config={REVIEW_GUIDELINES_CMS_CONFIG}
    />
  )
}
