import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { TERMS_OF_USE_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Terms of Use' },
]

export default function TermsOfUsePage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Terms of Use page"
      config={TERMS_OF_USE_CMS_CONFIG}
    />
  )
}
