import { useEffect, useRef, useState, type MouseEvent } from "react"
import { ImagePlus, X } from "lucide-react"
import { toast } from "sonner"
import { cn } from "@/lib/utils"

export type ImageDropzoneLayout = "square" | "banner"

export type ImageDropzoneProps = {
  value: File | null
  onChange: (file: File | null) => void
  /** Existing image URL (e.g. from API) when no new file is selected. */
  existingImageUrl?: string | null
  /** Called when user removes the existing image (no new file yet). */
  onClearExisting?: () => void
  disabled?: boolean
  error?: string
  layout?: ImageDropzoneLayout
  /** Shown inside the zone when empty (e.g. profile vs banner uploads). */
  placeholder?: string
  className?: string
}

const layoutClasses: Record<ImageDropzoneLayout, string> = {
  square: "aspect-square w-full max-w-[200px]",
  banner: "min-h-[120px] w-full",
}

export function ImageDropzone({
  value,
  onChange,
  existingImageUrl,
  onClearExisting,
  disabled = false,
  error,
  layout = "square",
  placeholder = "Click Or Drag Image to upload!",
  className,
}: ImageDropzoneProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [filePreview, setFilePreview] = useState<string | null>(null)
  const [dragOver, setDragOver] = useState(false)

  useEffect(() => {
    if (!value) {
      setFilePreview(null)
      return
    }
    const url = URL.createObjectURL(value)
    setFilePreview(url)
    return () => URL.revokeObjectURL(url)
  }, [value])

  const displayUrl = filePreview ?? existingImageUrl ?? null
  const hasImage = !!displayUrl

  const handleFile = (file: File | null) => {
    if (disabled) return
    if (!file) return onChange(null)
    if (!file.type.startsWith("image/")) {
      toast.error("Only image files are allowed")
      return
    }
    onChange(file)
  }

  const handleRemove = (e: MouseEvent) => {
    e.stopPropagation()
    if (disabled) return
    if (value) {
      handleFile(null)
      if (inputRef.current) inputRef.current.value = ""
    } else if (existingImageUrl) {
      onClearExisting?.()
    }
  }

  const openPicker = () => {
    if (!disabled) inputRef.current?.click()
  }

  return (
    <div className={className}>
      <div
        role={disabled ? undefined : "button"}
        tabIndex={disabled ? undefined : 0}
        onClick={openPicker}
        onKeyDown={(e) => {
          if (disabled) return
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault()
            openPicker()
          }
        }}
        onDragOver={(e) => {
          if (disabled) return
          e.preventDefault()
          setDragOver(true)
        }}
        onDragLeave={() => setDragOver(false)}
        onDrop={(e) => {
          if (disabled) return
          e.preventDefault()
          setDragOver(false)
          handleFile(e.dataTransfer.files?.[0] ?? null)
        }}
        className={cn(
          "relative flex items-center justify-center rounded-card border-2 border-dashed transition-colors",
          layoutClasses[layout],
          disabled
            ? "cursor-default border-gray-200 bg-gray-50"
            : "cursor-pointer",
          !disabled &&
            (dragOver
              ? "border-primary-500 bg-primary-100"
              : "border-primary-300 bg-primary-100/60 hover:border-primary-400"),
          error && "border-red-400 bg-red-50"
        )}
      >
        {hasImage ? (
          <>
            <img
              src={displayUrl}
              alt="Upload preview"
              className={cn(
                "h-full w-full rounded-card object-cover",
                layout === "banner" && "max-h-40 object-contain"
              )}
            />
            {!disabled && (
              <button
                type="button"
                onClick={handleRemove}
                className="absolute top-1.5 right-1.5 rounded-full bg-white/90 p-1 text-gray-700 shadow hover:bg-white"
                aria-label="Remove image"
              >
                <X size={14} />
              </button>
            )}
          </>
        ) : (
          <div className="flex flex-col items-center gap-2 px-4 text-center">
            <ImagePlus
              size={22}
              className={cn(disabled ? "text-gray-400" : "text-primary-600")}
            />
            <p className="text-xs leading-snug text-gray-700">{placeholder}</p>
          </div>
        )}

        <input
          ref={inputRef}
          type="file"
          accept="image/*"
          className="hidden"
          disabled={disabled}
          onChange={(e) => handleFile(e.target.files?.[0] ?? null)}
        />
      </div>
    </div>
  )
}
