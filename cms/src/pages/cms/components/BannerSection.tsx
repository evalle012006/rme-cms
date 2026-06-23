import { FormControl } from '@/components/atoms/FormControl'
import { ImagePreviewUpload } from '@/components/atoms/ImagePreviewUpload'

import { CmsSection } from './CmsSection'
import { sectionInputName } from './useCmsSectionState'

export type BannerSectionValues = {
  bannerImage: string | null
  bannerImagePath: string | null
  bannerImageFile: File | null
  title: string
}

export type BannerSectionProps = {
  sectionTitle: string
  initialValues?: BannerSectionValues
  onSave?: (values: BannerSectionValues) => Promise<void> | void
  className?: string
}

const defaultValues: BannerSectionValues = {
  bannerImage: null,
  bannerImagePath: null,
  bannerImageFile: null,
  title: '',
}

export function BannerSection({
  sectionTitle,
  initialValues = defaultValues,
  onSave,
  className,
}: BannerSectionProps) {
  return (
    <CmsSection
      sectionTitle={sectionTitle}
      initialValues={initialValues}
      onSave={onSave}
      className={className}
      bodyClassName="grid grid-cols-1 gap-6 lg:grid-cols-2"
    >
      {({ displayValues, setDraftValues }) => (
        <>
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
        </>
      )}
    </CmsSection>
  )
}
