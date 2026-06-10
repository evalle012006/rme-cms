import { useState } from 'react'
import { Button } from '@/components/atoms/Button'
import { MultiSelect, type MultiSelectOption } from '@/components/atoms/MultiSelect'
import { Card } from '@/components/ui/card'
import { cn } from '@/lib/utils'

export type CategoryRatingSectionProps = {
  categoryName: string
  options: MultiSelectOption[]
  value: number[]
  onChange: (value: number[]) => void
  onSave: () => void
  saving?: boolean
  disabled?: boolean
}

export function CategoryRatingSection({
  categoryName,
  options,
  value,
  onChange,
  onSave,
  saving = false,
  disabled = false,
}: CategoryRatingSectionProps) {
  const [dropdownOpen, setDropdownOpen] = useState(false)
  const fieldsDisabled = disabled || saving

  return (
    <Card
      className={cn(
        'overflow-visible p-0 shadow-none',
        dropdownOpen && 'relative z-30'
      )}
    >
      <div className="border-b border-gray-100 px-6 py-4">
        <h2 className="text-base font-semibold text-gray-800">{categoryName}</h2>
      </div>

      <div className="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start">
        <MultiSelect
          options={options}
          value={value}
          onChange={onChange}
          disabled={fieldsDisabled}
          onOpenChange={setDropdownOpen}
          className="min-w-0 flex-1"
        />

        <Button
          type="button"
          color="primary"
          disabled={fieldsDisabled}
          onClick={onSave}
          className="h-auto shrink-0 px-6 py-2.5 disabled:cursor-not-allowed disabled:opacity-60 sm:mt-0.5"
        >
          {saving ? 'Saving…' : 'Save'}
        </Button>
      </div>
    </Card>
  )
}
