import { FormControl } from '@/components/atoms/FormControl'

import { CmsSection } from './CmsSection'
import { sectionInputName } from './useCmsSectionState'

export type ReviewRatioSectionValues = {
  title: string
  trustOnlineReviews: string
  validateAdvise: string
}

export type ReviewRatioSectionProps = {
  sectionTitle: string
  initialValues?: ReviewRatioSectionValues
  onSave?: (values: ReviewRatioSectionValues) => Promise<void> | void
  className?: string
}

const defaultValues: ReviewRatioSectionValues = {
  title: '',
  trustOnlineReviews: '',
  validateAdvise: '',
}

export function ReviewRatioSection({
  sectionTitle,
  initialValues = defaultValues,
  onSave,
  className,
}: ReviewRatioSectionProps) {
  const slug = sectionInputName(sectionTitle).replace(/-title$/, '')

  return (
    <CmsSection
      sectionTitle={sectionTitle}
      initialValues={initialValues}
      onSave={onSave}
      className={className}
      bodyClassName="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
      {({ displayValues, setDraftValues }) => {
        const handleFieldChange = (
          field: keyof ReviewRatioSectionValues,
          value: string
        ) => {
          setDraftValues((current) => ({ ...current, [field]: value }))
        }

        return (
          <>
            <FormControl
              label="Title"
              name={`${slug}-title`}
              value={displayValues.title}
              onChange={(event) => handleFieldChange('title', event.target.value)}
            />
            <FormControl
              label="Trust online reviews (%)"
              name={`${slug}-trust-online-reviews`}
              type="number"
              min={0}
              max={100}
              value={displayValues.trustOnlineReviews}
              onChange={(event) =>
                handleFieldChange('trustOnlineReviews', event.target.value)
              }
            />
            <FormControl
              label="Validate advise (%)"
              name={`${slug}-validate-advise`}
              type="number"
              min={0}
              max={100}
              value={displayValues.validateAdvise}
              onChange={(event) =>
                handleFieldChange('validateAdvise', event.target.value)
              }
            />
          </>
        )
      }}
    </CmsSection>
  )
}
