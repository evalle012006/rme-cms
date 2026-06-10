import { FormControl } from '@/components/atoms/FormControl'

import { CmsSection } from './CmsSection'
import { DescriptionBox } from './DescriptionBox'
import { sectionInputName } from './useCmsSectionState'

export type MissionSectionValues = {
  title: string
  descriptions: [string, string, string, string]
}

export type MissionSectionProps = {
  sectionTitle: string
  initialValues?: MissionSectionValues
  onSave?: (values: MissionSectionValues) => Promise<void> | void
  className?: string
}

const defaultValues: MissionSectionValues = {
  title: '',
  descriptions: ['', '', '', ''],
}

const descriptionLabels = [
  'Description 1',
  'Description 2',
  'Description 3',
  'Description 4',
] as const

export function MissionSection({
  sectionTitle,
  initialValues = defaultValues,
  onSave,
  className,
}: MissionSectionProps) {
  return (
    <CmsSection
      sectionTitle={sectionTitle}
      initialValues={initialValues}
      onSave={onSave}
      className={className}
      bodyClassName="space-y-6"
    >
      {({ displayValues, setDraftValues }) => {
        const handleDescriptionChange = (index: number, html: string) => {
          setDraftValues((current) => {
            const descriptions = [...current.descriptions] as [
              string,
              string,
              string,
              string,
            ]
            descriptions[index] = html
            return { ...current, descriptions }
          })
        }

        return (
          <>
            <FormControl
              label="Title"
              name={sectionInputName(sectionTitle)}
              value={displayValues.title}
              onChange={(event) =>
                setDraftValues((current) => ({ ...current, title: event.target.value }))
              }
            />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
              {descriptionLabels.map((label, index) => (
                <DescriptionBox
                  key={label}
                  label={label}
                  value={displayValues.descriptions[index]}
                  onChange={(html) => handleDescriptionChange(index, html)}
                />
              ))}
            </div>
          </>
        )
      }}
    </CmsSection>
  )
}
