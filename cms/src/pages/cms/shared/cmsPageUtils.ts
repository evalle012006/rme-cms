import type { BannerSectionValues } from '@/pages/cms/components/BannerSection'
import type { BannerTitleDescriptionSectionValues } from '@/pages/cms/components/BannerTitleDescriptionSection'
import type { MissionSectionValues } from '@/pages/cms/components/MissionSection'
import type { ReviewRatioSectionValues } from '@/pages/cms/components/ReviewRatioSection'
import type { TitleDescriptionSectionValues } from '@/pages/cms/components/TitleDescriptionSection'
import {
  getCmsImageUrl,
  parseCmsOtherParams,
  type CmsPageSection,
} from '@/api/cms/cmsAPIHandler'

export type CmsSectionType =
  | 'banner'
  | 'reviewRatio'
  | 'bannerTitleDescription'
  | 'titleDescription'
  | 'mission'

export interface CmsPageSectionConfig {
  id: number
  key: string
  apiSection: string
  apiSlug?: string
  uiTitle: string
  type: CmsSectionType
}

export interface CmsPageConfig {
  page: string
  sections: CmsPageSectionConfig[]
}

export type CmsSectionValues =
  | BannerSectionValues
  | ReviewRatioSectionValues
  | BannerTitleDescriptionSectionValues
  | TitleDescriptionSectionValues
  | MissionSectionValues

/** Converts API section names to snake_case slugs for matching. */
export function toSectionSlug(value: string): string {
  return value
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/['']/g, '')
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
}

function camelToSnake(value: string): string {
  return value.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase()
}

function sectionSlugCandidates(config: CmsPageSectionConfig): string[] {
  const slugs = new Set<string>()

  if (config.apiSlug) slugs.add(config.apiSlug)
  slugs.add(toSectionSlug(config.apiSection))
  slugs.add(camelToSnake(config.key))

  if (config.key === 'recentReview') slugs.add('recent_reviews')

  // Legacy / generated slugs that may exist alongside the short DB/Blade slugs
  if (config.key === 'propertyProjectBuilders') {
    slugs.add('reviews_on_rme')
    slugs.add('property_project_and_builders_reviews_on_ratemyestate')
  }
  if (config.key === 'communityGuidelines') {
    slugs.add('guidelines')
    slugs.add('our_community_guidelines')
  }
  if (config.key === 'reviewDosDonts') {
    slugs.add('reviews_dos')
    slugs.add('review_dos_and_donts')
  }
  if (config.key === 'moderation') {
    slugs.add('moderation_works')
    slugs.add('how_does_moderation_work')
  }
  if (config.key === 'reviewNotVisible') {
    slugs.add('wrote_a_review')
    slugs.add('i_wrote_a_review_but_i_cant_see_it_on_the_website_why_not')
  }

  return [...slugs]
}

export function resolveSectionSlug(config: CmsPageSectionConfig): string {
  return config.apiSlug ?? toSectionSlug(config.apiSection)
}

export function findCmsSection(
  sections: CmsPageSection[],
  config: CmsPageSectionConfig
): CmsPageSection | undefined {
  const slugCandidates = sectionSlugCandidates(config)

  return (
    sections.find(
      (section) =>
        section.section_slug != null && slugCandidates.includes(section.section_slug)
    ) ??
    sections.find((section) => section.section_name === config.apiSection) ??
    sections.find((section) => section.section === config.apiSection) ??
    sections.find((section) => section.id === config.id)
  )
}

export function resolveImagePayload(
  file: File | null,
  path: string | null
): File | string | undefined {
  if (file) return file
  if (path) return path
  return undefined
}

const emptyBannerValues = (): BannerSectionValues => ({
  bannerImage: null,
  bannerImagePath: null,
  bannerImageFile: null,
  title: '',
})

const emptyBannerTitleDescriptionValues = (): BannerTitleDescriptionSectionValues => ({
  bannerImage: null,
  bannerImagePath: null,
  bannerImageFile: null,
  title: '',
  description: '',
})

const emptyReviewRatioValues = (): ReviewRatioSectionValues => ({
  title: '',
  trustOnlineReviews: '',
  validateAdvise: '',
})

const emptyTitleDescriptionValues = (): TitleDescriptionSectionValues => ({
  title: '',
  description: '',
})

const emptyMissionValues = (): MissionSectionValues => ({
  title: '',
  descriptions: ['', '', '', ''],
})

export function mapCmsSectionValues(
  type: CmsSectionType,
  section?: CmsPageSection
): CmsSectionValues {
  switch (type) {
    case 'banner':
      return mapBannerSection(section)
    case 'reviewRatio':
      return mapReviewRatioSection(section)
    case 'bannerTitleDescription':
      return mapBannerTitleDescriptionSection(section)
    case 'titleDescription':
      return mapTitleDescriptionSection(section)
    case 'mission':
      return mapMissionSection(section)
  }
}

function mapBannerSection(section?: CmsPageSection): BannerSectionValues {
  if (!section) return emptyBannerValues()

  return {
    title: section.title ?? '',
    bannerImage: getCmsImageUrl(section),
    bannerImagePath: section.image,
    bannerImageFile: null,
  }
}

function mapReviewRatioSection(section?: CmsPageSection): ReviewRatioSectionValues {
  if (!section) return emptyReviewRatioValues()

  const otherParams = parseCmsOtherParams(section.other_params)

  return {
    title: section.title ?? '',
    trustOnlineReviews:
      section.trust_online_ratio ?? otherParams.trust_online_ratio ?? '',
    validateAdvise:
      section.validate_reviews_ratio ?? otherParams.validate_reviews_ratio ?? '',
  }
}

function mapBannerTitleDescriptionSection(
  section?: CmsPageSection
): BannerTitleDescriptionSectionValues {
  if (!section) return emptyBannerTitleDescriptionValues()

  return {
    title: section.title ?? '',
    description: section.description ?? '',
    bannerImage: getCmsImageUrl(section),
    bannerImagePath: section.image,
    bannerImageFile: null,
  }
}

function mapTitleDescriptionSection(
  section?: CmsPageSection
): TitleDescriptionSectionValues {
  if (!section) return emptyTitleDescriptionValues()

  return {
    title: section.title ?? '',
    description: section.description ?? '',
  }
}

function mapMissionSection(section?: CmsPageSection): MissionSectionValues {
  if (!section) return emptyMissionValues()

  const otherParams = parseCmsOtherParams(section.other_params)

  return {
    title: section.title ?? '',
    descriptions: [
      otherParams.card_1 ?? otherParams.description_1 ?? section.description ?? '',
      otherParams.card_2 ?? otherParams.description_2 ?? '',
      otherParams.card_3 ?? otherParams.description_3 ?? '',
      otherParams.card_4 ?? otherParams.description_4 ?? '',
    ] as [string, string, string, string],
  }
}

export function buildCmsSavePayload(
  config: CmsPageSectionConfig,
  section: CmsPageSection | undefined,
  values: CmsSectionValues
) {
  const id = section?.id ?? config.id
  const base = {
    id,
    section: config.apiSection,
    section_slug: resolveSectionSlug(config),
  }

  switch (config.type) {
    case 'banner': {
      const bannerValues = values as BannerSectionValues
      const image = resolveImagePayload(
        bannerValues.bannerImageFile,
        bannerValues.bannerImagePath
      )
      return {
        ...base,
        title: bannerValues.title,
        ...(image ? { image } : {}),
      }
    }
    case 'reviewRatio': {
      const ratioValues = values as ReviewRatioSectionValues
      return {
        ...base,
        title: ratioValues.title,
        trust_online_ratio: ratioValues.trustOnlineReviews,
        validate_reviews_ratio: ratioValues.validateAdvise,
        other_params: JSON.stringify({
          trust_online_ratio: ratioValues.trustOnlineReviews,
          validate_reviews_ratio: ratioValues.validateAdvise,
        }),
      }
    }
    case 'bannerTitleDescription': {
      const bannerTitleValues = values as BannerTitleDescriptionSectionValues
      const image = resolveImagePayload(
        bannerTitleValues.bannerImageFile,
        bannerTitleValues.bannerImagePath
      )
      return {
        ...base,
        title: bannerTitleValues.title,
        body: bannerTitleValues.description,
        ...(image ? { image } : {}),
      }
    }
    case 'titleDescription': {
      const titleDescriptionValues = values as TitleDescriptionSectionValues
      return {
        ...base,
        title: titleDescriptionValues.title,
        body: titleDescriptionValues.description,
      }
    }
    case 'mission': {
      const missionValues = values as MissionSectionValues
      return {
        ...base,
        title: missionValues.title,
        body: missionValues.descriptions[0],
        other_params: JSON.stringify({
          card_1: missionValues.descriptions[0],
          card_2: missionValues.descriptions[1],
          card_3: missionValues.descriptions[2],
          card_4: missionValues.descriptions[3],
        }),
      }
    }
  }
}

export const HOME_CMS_CONFIG: CmsPageConfig = {
  page: 'home',
  sections: [
    {
      id: 1,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
    {
      id: 2,
      key: 'reviewRatio',
      apiSection: 'Review ratio',
      apiSlug: 'review_ratio',
      uiTitle: 'Review ratio section',
      type: 'reviewRatio',
    },
    {
      id: 3,
      key: 'whyWriteReview',
      apiSection: 'Why write review',
      apiSlug: 'why_write_review',
      uiTitle: 'Why write review section',
      type: 'bannerTitleDescription',
    },
    {
      id: 4,
      key: 'recentReview',
      apiSection: 'Recent reviews',
      apiSlug: 'recent_reviews',
      uiTitle: 'Recent reviews section',
      type: 'titleDescription',
    },
    {
      id: 5,
      key: 'ourBlogs',
      apiSection: 'Our blogs',
      apiSlug: 'our_blogs',
      uiTitle: 'Our blogs section',
      type: 'titleDescription',
    },
  ],
}

export const ABOUT_CMS_CONFIG: CmsPageConfig = {
  page: 'about',
  sections: [
    {
      id: 6,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
    {
      id: 7,
      key: 'getToKnow',
      apiSection: 'Get to know',
      apiSlug: 'get_to_know',
      uiTitle: 'Get to know section',
      type: 'bannerTitleDescription',
    },
    {
      id: 8,
      key: 'ourMission',
      apiSection: 'Our mission',
      apiSlug: 'our_mission',
      uiTitle: 'Our mission section',
      type: 'mission',
    },
    {
      id: 9,
      key: 'ourPlatform',
      apiSection: 'Our platform',
      apiSlug: 'our_platform',
      uiTitle: 'Our platform section',
      type: 'bannerTitleDescription',
    },
    {
      id: 10,
      key: 'recentReview',
      apiSection: 'Recent reviews',
      apiSlug: 'recent_reviews',
      uiTitle: 'Recent reviews section',
      type: 'titleDescription',
    },
  ],
}

export const PROPERTY_GUIDE_CMS_CONFIG: CmsPageConfig = {
  page: 'property_guide',
  sections: [
    {
      id: 11,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
  ],
}

export const PROPERTY_COACH_CMS_CONFIG: CmsPageConfig = {
  page: 'property_coach',
  sections: [
    {
      id: 12,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
  ],
}

export const CONTACT_CMS_CONFIG: CmsPageConfig = {
  page: 'contact',
  sections: [
    {
      id: 13,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
  ],
}

export const REVIEW_GUIDELINES_CMS_CONFIG: CmsPageConfig = {
  page: 'review_guidelines',
  sections: [
    {
      id: 14,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
    {
      id: 15,
      key: 'propertyProjectBuilders',
      apiSection: 'Property, Project & Builders reviews on ratemyestate',
      apiSlug: 'reviews_on_rme',
      uiTitle: 'Property, Project & Builders reviews on ratemyestate section',
      type: 'titleDescription',
    },
    {
      id: 16,
      key: 'communityGuidelines',
      apiSection: 'Our community guidelines',
      apiSlug: 'guidelines',
      uiTitle: 'Our community guidelines section',
      type: 'titleDescription',
    },
    {
      id: 17,
      key: 'reviewDosDonts',
      apiSection: "Review dos and don'ts",
      apiSlug: 'reviews_dos',
      uiTitle: "Review dos and don'ts section",
      type: 'titleDescription',
    },
    {
      id: 18,
      key: 'moderation',
      apiSection: 'How does moderation work?',
      apiSlug: 'moderation_works',
      uiTitle: 'How does moderation work? section',
      type: 'titleDescription',
    },
    {
      id: 19,
      key: 'reviewNotVisible',
      apiSection: "I wrote a review but I can't see it on the website - why not ?",
      apiSlug: 'wrote_a_review',
      uiTitle: "I wrote a review but I can't see it on the website - why not ? section",
      type: 'titleDescription',
    },
  ],
}

export const TERMS_OF_USE_CMS_CONFIG: CmsPageConfig = {
  page: 'terms_of_use',
  sections: [
    {
      id: 20,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
    {
      id: 21,
      key: 'general',
      apiSection: 'General',
      apiSlug: 'general',
      uiTitle: 'General section',
      type: 'titleDescription',
    },
    {
      id: 22,
      key: 'copyright',
      apiSection: 'Copyright',
      apiSlug: 'copyright',
      uiTitle: 'Copyright section',
      type: 'titleDescription',
    },
    {
      id: 23,
      key: 'disclaimer',
      apiSection: 'Disclaimer',
      apiSlug: 'disclaimer',
      uiTitle: 'Disclaimer section',
      type: 'titleDescription',
    },
  ],
}

export const REVIEWS_LISTING_CMS_CONFIG: CmsPageConfig = {
  page: 'reviews_listing',
  sections: [
    {
      id: 28,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
  ],
}

export const PRIVACY_POLICY_CMS_CONFIG: CmsPageConfig = {
  page: 'privacy_policy',
  sections: [
    {
      id: 24,
      key: 'banner',
      apiSection: 'Banner',
      apiSlug: 'banner',
      uiTitle: 'Banner section',
      type: 'banner',
    },
    {
      id: 25,
      key: 'privacyCommitment',
      apiSection: 'Our commitment to maintaining your privacy',
      apiSlug: 'your_privacy',
      uiTitle: 'Our commitment to maintaining your privacy section',
      type: 'titleDescription',
    },
    {
      id: 26,
      key: 'definitions',
      apiSection: 'Definitions',
      apiSlug: 'definitions',
      uiTitle: 'Definitions section',
      type: 'titleDescription',
    },
    {
      id: 27,
      key: 'collectingInfo',
      apiSection: 'Collecting other information on Ratemyestate visitors',
      apiSlug: 'collecting_info',
      uiTitle: 'Collecting other information on Ratemyestate visitors section',
      type: 'titleDescription',
    },
  ],
}
