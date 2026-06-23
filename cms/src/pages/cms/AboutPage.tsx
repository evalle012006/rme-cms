import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { ABOUT_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'About' },
]

export default function AboutPage() {
  return <CmsPageShell breadcrumb={BREADCRUMB} title="About page" config={ABOUT_CMS_CONFIG} />
}
