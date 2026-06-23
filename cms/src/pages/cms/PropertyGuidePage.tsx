import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { PROPERTY_GUIDE_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Property Guide' },
]

export default function PropertyGuidePage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Property Guide page"
      config={PROPERTY_GUIDE_CMS_CONFIG}
    />
  )
}
