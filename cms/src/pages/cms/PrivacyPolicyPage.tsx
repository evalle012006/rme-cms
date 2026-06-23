import { CmsPageShell } from '@/pages/cms/shared/CmsPageShell'
import { PRIVACY_POLICY_CMS_CONFIG } from '@/pages/cms/shared/cmsPageUtils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Cms', to: ROUTES.cms.home },
  { label: 'Privacy Policy' },
]

export default function PrivacyPolicyPage() {
  return (
    <CmsPageShell
      breadcrumb={BREADCRUMB}
      title="Privacy Policy page"
      config={PRIVACY_POLICY_CMS_CONFIG}
    />
  )
}
