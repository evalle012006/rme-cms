import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { HOME_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
]

export default function HomePage() {
  return <CmsPageShell breadcrumb={BREADCRUMB} title="Home page" config={HOME_CMS_CONFIG} />
}
