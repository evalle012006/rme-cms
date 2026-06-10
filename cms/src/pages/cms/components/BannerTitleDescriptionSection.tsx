import { FormControl } from '@/components/atoms/FormControl'
import { ImagePreviewUpload } from '@/components/atoms/ImagePreviewUpload'

import { CmsSection } from './CmsSection'
import { DescriptionBox } from './DescriptionBox'
import { sectionInputName } from './useCmsSectionState'

export type BannerTitleDescriptionSectionValues = {
  bannerImage: string | null
  bannerImagePath: string | null
  bannerImageFile: File | null
  title: string
  description: string
}

export type BannerTitleDescriptionSectionProps = {
  sectionTitle: string
  initialValues?: BannerTitleDescriptionSectionValues
  onSave?: (values: BannerTitleDescriptionSectionValues) => Promise<void> | void
  className?: string
}

const defaultValues: BannerTitleDescriptionSectionValues = {
  bannerImage: null,
  bannerImagePath: null,
  bannerImageFile: null,
  title: '',
  description: '',
}

export function BannerTitleDescriptionSection({
  sectionTitle,
  initialValues = defaultValues,
  onSave,
  className,
}: BannerTitleDescriptionSectionProps) {
  return (
    <CmsSection
      sectionTitle={sectionTitle}
      initialValues={initialValues}
      onSave={onSave}
      className={className}
      bodyClassName="space-y-6"
    >
      {({ displayValues, setDraftValues }) => (
        <>
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <ImagePreviewUpload
              layout="banner"
              value={displayValues.bannerImage}
              onChange={(file, previewUrl) =>
                setDraftValues((current) => ({
                  ...current,
                  bannerImage: previewUrl,
                  bannerImageFile: file,
                  bannerImagePath: file ? current.bannerImagePath : null,
                }))
              }
            />
            <FormControl
              label="Title"
              name={sectionInputName(sectionTitle)}
              value={displayValues.title}
              onChange={(event) =>
                setDraftValues((current) => ({ ...current, title: event.target.value }))
              }
            />
          </div>

          <DescriptionBox
            value={displayValues.description}
            onChange={(html) =>
              setDraftValues((current) => ({ ...current, description: html }))
            }
          />
        </>
      )}
    </CmsSection>
  )
}
