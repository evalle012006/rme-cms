import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { CONTACT_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Contact' },
]

export default function ContactPage() {
  return (
    <CmsPageShell breadcrumb={BREADCRUMB} title="Contact page" config={CONTACT_CMS_CONFIG} />
  )
}
