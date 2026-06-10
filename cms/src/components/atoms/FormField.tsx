import { cn } from "@/lib/utils"
import type { ReactNode } from "react"

export type FormFieldLayout = "stacked" | "row"

export type FormFieldProps = {
  label?: string
  children: ReactNode
  error?: string
  required?: boolean
  htmlFor?: string
  layout?: FormFieldLayout
  className?: string
  contentClassName?: string
}

export function fieldId(label?: string, id?: string) {
  return id ?? label?.toLowerCase().replace(/\s+/g, "-")
}

export function FormField({
  label,
  children,
  error,
  required,
  htmlFor,
  layout = "stacked",
  className,
  contentClassName,
}: FormFieldProps) {
  const labelEl =
    label != null ? (
      <label
        htmlFor={htmlFor}
        className={cn(
          "block text-sm font-medium text-gray-700",
          layout === "stacked" && "mb-1.5"
        )}
      >
        {label}
        {required && <span className="text-red-500"> *</span>}
      </label>
    ) : null

  if (layout === "row") {
    return (
      <div className={cn("flex flex-col gap-1.5 px-6 py-5", className)}>
        {labelEl}
        <div className={cn("min-w-0 w-full", contentClassName)}>
          {children}
          {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </div>
      </div>
    )
  }

  return (
    <div className={className}>
      {labelEl}
      <div className={contentClassName}>{children}</div>
      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </div>
  )
}
