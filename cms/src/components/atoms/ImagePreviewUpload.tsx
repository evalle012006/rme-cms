import { useRef, useState, type ChangeEvent, type DragEvent } from 'react'
import { cn } from '@/lib/utils'

export type ImagePreviewUploadLayout = 'banner' | 'profile'

export type ImagePreviewUploadProps = {
  label?: string
  value?: string | null
  onChange?: (file: File | null, previewUrl: string | null) => void
  disabled?: boolean
  layout?: ImagePreviewUploadLayout
  className?: string
}

const layoutConfig: Record<
  ImagePreviewUploadLayout,
  { minHeight: string; imgMaxHeight: string; defaultLabel: string; emptyDisabled: string }
> = {
  banner: {
    minHeight: 'min-h-[200px]',
    imgMaxHeight: 'max-h-48',
    defaultLabel: 'Banner Image',
    emptyDisabled: 'No banner image',
  },
  profile: {
    minHeight: 'min-h-[140px]',
    imgMaxHeight: 'max-h-28',
    defaultLabel: 'Profile Image',
    emptyDisabled: 'No profile image',
  },
}

/** Drag-and-drop image field that stores a preview URL (CMS sections, profile). */
export function ImagePreviewUpload({
  label,
  value,
  onChange,
  disabled = false,
  layout = 'banner',
  className,
}: ImagePreviewUploadProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [isDragging, setIsDragging] = useState(false)
  const config = layoutConfig[layout]
  const resolvedLabel = label ?? config.defaultLabel

  const handleFile = (file: File | null) => {
    if (!file || disabled) return
    if (!file.type.startsWith('image/')) return
    onChange?.(file, URL.createObjectURL(file))
  }

  const handleInputChange = (event: ChangeEvent<HTMLInputElement>) => {
    handleFile(event.target.files?.[0] ?? null)
  }

  const handleDrop = (event: DragEvent<HTMLDivElement>) => {
    event.preventDefault()
    setIsDragging(false)
    handleFile(event.dataTransfer.files?.[0] ?? null)
  }

  return (
    <div className={className}>
      <p className="mb-1.5 text-sm font-medium text-gray-700">{resolvedLabel}</p>
      <div
        role="button"
        tabIndex={disabled ? -1 : 0}
        onClick={() => !disabled && inputRef.current?.click()}
        onKeyDown={(event) => {
          if (!disabled && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault()
            inputRef.current?.click()
          }
        }}
        onDragOver={(event) => {
          event.preventDefault()
          if (!disabled) setIsDragging(true)
        }}
        onDragLeave={() => setIsDragging(false)}
        onDrop={handleDrop}
        className={cn(
          'flex items-center justify-center rounded-md border-2 border-dashed px-4 py-8 text-center transition',
          config.minHeight,
          disabled
            ? 'cursor-default border-gray-200 bg-gray-50'
            : 'cursor-pointer border-primary-200 bg-primary-50 hover:border-primary-300',
          isDragging && !disabled && 'border-primary-400 bg-primary-100'
        )}
      >
        {value ? (
          <img
            src={value}
            alt={`${resolvedLabel} preview`}
            className={cn('max-w-full rounded object-contain', config.imgMaxHeight)}
          />
        ) : (
          <p className="text-sm text-gray-500">
            {disabled ? config.emptyDisabled : 'Click Or Drag Image to upload!'}
          </p>
        )}
      </div>
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        className="hidden"
        disabled={disabled}
        onChange={handleInputChange}
      />
    </div>
  )
}
