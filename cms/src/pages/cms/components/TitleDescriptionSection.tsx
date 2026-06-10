import { FormControl } from '@/components/atoms/FormControl'

import { CmsSection } from './CmsSection'
import { DescriptionBox } from './DescriptionBox'
import { sectionInputName } from './useCmsSectionState'

export type TitleDescriptionSectionValues = {
  title: string
  description: string
}

export type TitleDescriptionSectionProps = {
  sectionTitle: string
  initialValues?: TitleDescriptionSectionValues
  onSave?: (values: TitleDescriptionSectionValues) => Promise<void> | void
  className?: string
}

const defaultValues: TitleDescriptionSectionValues = {
  title: '',
  description: '',
}

export function TitleDescriptionSection({
  sectionTitle,
  initialValues = defaultValues,
  onSave,
  className,
}: TitleDescriptionSectionProps) {
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
          <FormControl
            label="Title"
            name={sectionInputName(sectionTitle)}
            value={displayValues.title}
            onChange={(event) =>
              setDraftValues((current) => ({ ...current, title: event.target.value }))
            }
          />

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
