import { useEffect, useRef, useState } from 'react'
import { toast } from 'sonner'

function serializeValues<T>(values: T): string {
  return JSON.stringify(values, (_key, value) => {
    if (value instanceof File) {
      return `${value.name}:${value.size}:${value.lastModified}`
    }
    return value
  })
}

export function useCmsSectionState<T>(
  initialValues: T,
  sectionTitle: string,
  onSave?: (values: T) => Promise<void> | void
) {
  const [savedValues, setSavedValues] = useState(initialValues)
  const [draftValues, setDraftValues] = useState(initialValues)
  const [saving, setSaving] = useState(false)
  const initialValuesRef = useRef(serializeValues(initialValues))

  useEffect(() => {
    const nextSerialized = serializeValues(initialValues)
    if (initialValuesRef.current === nextSerialized) return

    initialValuesRef.current = nextSerialized
    setSavedValues(initialValues)
    setDraftValues(initialValues)
  }, [initialValues])

  const hasChanges = serializeValues(draftValues) !== serializeValues(savedValues)

  const handleSave = async () => {
    if (!hasChanges) return

    setSaving(true)
    try {
      await onSave?.(draftValues)
      setSavedValues(draftValues)
      toast.success(`${sectionTitle} saved successfully`)
    } catch {
      toast.error(`Failed to save ${sectionTitle.toLowerCase()}`)
    } finally {
      setSaving(false)
    }
  }

  return {
    displayValues: draftValues,
    setDraftValues,
    hasChanges,
    saving,
    handleSave,
  }
}

export function sectionInputName(sectionTitle: string) {
  return `${sectionTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-title`
}
