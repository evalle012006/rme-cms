import type { ReactNode } from 'react'

import { Button } from '@/components/atoms/Button'
import { PageCard } from '@/components/atoms/PageCard'
import { cn } from '@/lib/utils'

import { useCmsSectionState } from './useCmsSectionState'

export type CmsSectionContext<T> = {
  displayValues: T
  setDraftValues: React.Dispatch<React.SetStateAction<T>>
}

export type CmsSectionProps<T> = {
  sectionTitle: string
  initialValues: T
  onSave?: (values: T) => Promise<void> | void
  className?: string
  bodyClassName?: string
  children: (ctx: CmsSectionContext<T>) => ReactNode
}

export function CmsSection<T>({
  sectionTitle,
  initialValues,
  onSave,
  className,
  bodyClassName,
  children,
}: CmsSectionProps<T>) {
  const { displayValues, setDraftValues, hasChanges, saving, handleSave } =
    useCmsSectionState(initialValues, sectionTitle, onSave)

  const actions = (
    <Button
      size="lg"
      type="button"
      color="primary"
      disabled={!hasChanges || saving}
      onClick={handleSave}
      className="min-w-[100px] px-6"
    >
      {saving ? 'Saving…' : 'Save'}
    </Button>
  )

  return (
    <PageCard
      title={sectionTitle}
      actions={actions}
      overflowVisible
      className={cn(className)}
      bodyClassName={bodyClassName}
    >
      {children({ displayValues, setDraftValues })}
    </PageCard>
  )
}
