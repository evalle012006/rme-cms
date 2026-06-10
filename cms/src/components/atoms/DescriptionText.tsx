import { cn } from "@/lib/utils"
import type { ComponentProps } from "react"

const sizeClasses = {
  sm: "text-xs",
  md: "text-sm",
  lg: "text-base",
} as const

const toneClasses = {
  muted: "text-gray-500",
  default: "text-gray-700",
  subtle: "text-gray-400",
} as const

export type DescriptionTextSize = keyof typeof sizeClasses
export type DescriptionTextTone = keyof typeof toneClasses

export type DescriptionTextProps = ComponentProps<"p"> & {
  size?: DescriptionTextSize
  tone?: DescriptionTextTone
  className?: string
}

export function DescriptionText({
  size = "md",
  tone = "muted",
  className,
  ...props
}: DescriptionTextProps) {
  return (
    <p className={cn(sizeClasses[size], toneClasses[tone], className)} {...props} />
  )
}
