import { useRef, useState, type DragEvent, type ChangeEvent } from 'react'
import { cn } from '@/lib/utils'

export type ImageUploadProps = {
  label?: string
  value?: string | null
  onChange?: (file: File | null, previewUrl: string | null) => void
  disabled?: boolean
  className?: string
}

export function ImageUpload({
  label = 'Profile Image',
  value,
  onChange,
  disabled = false,
  className,
}: ImageUploadProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [isDragging, setIsDragging] = useState(false)

  const handleFile = (file: File | null) => {
    if (!file || disabled) return
    if (!file.type.startsWith('image/')) return

    const previewUrl = URL.createObjectURL(file)
    onChange?.(file, previewUrl)
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
      <p className="mb-1.5 text-sm font-medium text-gray-700">{label}</p>
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
          'flex min-h-[140px] items-center justify-center rounded-md border-2 border-dashed px-4 py-6 text-center transition',
          disabled
            ? 'cursor-default border-gray-200 bg-gray-50'
            : 'cursor-pointer border-primary-200 bg-primary-50 hover:border-primary-300',
          isDragging && !disabled && 'border-primary-400 bg-primary-100'
        )}
      >
        {value ? (
          <img src={value} alt="Profile preview" className="max-h-28 max-w-full rounded object-contain" />
        ) : (
          <p className="text-sm text-gray-500">
            {disabled ? 'No profile image' : 'Click Or Drag Image to upload!'}
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
