import {
  BannerSection,
  type BannerSectionValues,
} from '@/pages/cms/components/BannerSection'
import {
  BannerTitleDescriptionSection,
  type BannerTitleDescriptionSectionValues,
} from '@/pages/cms/components/BannerTitleDescriptionSection'
import { CmsSectionSkeleton } from '@/pages/cms/components/CmsSectionSkeleton'
import {
  MissionSection,
  type MissionSectionValues,
} from '@/pages/cms/components/MissionSection'
import {
  ReviewRatioSection,
  type ReviewRatioSectionValues,
} from '@/pages/cms/components/ReviewRatioSection'
import {
  TitleDescriptionSection,
  type TitleDescriptionSectionValues,
} from '@/pages/cms/components/TitleDescriptionSection'
import type { CmsPageSectionConfig } from '@/pages/cms/shared/cmsPageUtils'
import type { useCmsPage } from '@/pages/cms/shared/useCmsPage'

type CmsPageSectionsProps = {
  cms: ReturnType<typeof useCmsPage>
}

function renderSection(config: CmsPageSectionConfig, cms: ReturnType<typeof useCmsPage>) {
  switch (config.type) {
    case 'banner':
      return (
        <BannerSection
          key={config.key}
          sectionTitle={config.uiTitle}
          initialValues={cms.getValues<BannerSectionValues>(config.key)}
          onSave={cms.getSaveHandler<BannerSectionValues>(config.key)}
        />
      )
    case 'reviewRatio':
      return (
        <ReviewRatioSection
          key={config.key}
          sectionTitle={config.uiTitle}
          initialValues={cms.getValues<ReviewRatioSectionValues>(config.key)}
          onSave={cms.getSaveHandler<ReviewRatioSectionValues>(config.key)}
        />
      )
    case 'bannerTitleDescription':
      return (
        <BannerTitleDescriptionSection
          key={config.key}
          sectionTitle={config.uiTitle}
          initialValues={cms.getValues<BannerTitleDescriptionSectionValues>(config.key)}
          onSave={cms.getSaveHandler<BannerTitleDescriptionSectionValues>(config.key)}
        />
      )
    case 'titleDescription':
      return (
        <TitleDescriptionSection
          key={config.key}
          sectionTitle={config.uiTitle}
          initialValues={cms.getValues<TitleDescriptionSectionValues>(config.key)}
          onSave={cms.getSaveHandler<TitleDescriptionSectionValues>(config.key)}
        />
      )
    case 'mission':
      return (
        <MissionSection
          key={config.key}
          sectionTitle={config.uiTitle}
          initialValues={cms.getValues<MissionSectionValues>(config.key)}
          onSave={cms.getSaveHandler<MissionSectionValues>(config.key)}
        />
      )
  }
}

export function CmsPageSections({ cms }: CmsPageSectionsProps) {
  if (cms.loading && !cms.loaded) {
    return (
      <>
        {cms.sections.map((config) => (
          <CmsSectionSkeleton
            key={config.key}
            type={config.type}
            sectionTitle={config.uiTitle}
          />
        ))}
      </>
    )
  }

  return <>{cms.sections.map((config) => renderSection(config, cms))}</>
}
