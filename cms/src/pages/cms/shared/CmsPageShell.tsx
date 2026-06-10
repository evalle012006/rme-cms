import { Breadcrumb, type BreadcrumbItem } from '@/components/atoms/Breadcrumb'
import { HeaderText } from '@/components/atoms/HeaderText'
import { CmsPageSections } from '@/pages/cms/shared/CmsPageSections'
import type { CmsPageConfig } from '@/pages/cms/shared/cmsPageUtils'
import { useCmsPage } from '@/pages/cms/shared/useCmsPage'

type CmsPageShellProps = {
  breadcrumb: BreadcrumbItem[]
  title: string
  config: CmsPageConfig
}

export function CmsPageShell({ breadcrumb, title, config }: CmsPageShellProps) {
  const cms = useCmsPage(config)

  return (
    <div className="space-y-6 pb-12">
      <Breadcrumb items={breadcrumb} />

      <HeaderText size="xl">{title}</HeaderText>

      <div className="space-y-6">
        <CmsPageSections cms={cms} />
      </div>
    </div>
  )
}
