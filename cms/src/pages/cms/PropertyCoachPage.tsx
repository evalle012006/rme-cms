import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { PROPERTY_COACH_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Property Blog' },
]

export default function PropertyCoachPage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Property Blog page"
      config={PROPERTY_COACH_CMS_CONFIG}
    />
  )
}
