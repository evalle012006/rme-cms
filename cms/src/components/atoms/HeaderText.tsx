import { cn } from "@/lib/utils"
import type { ComponentPropsWithoutRef, ElementType } from "react"

const sizeClasses = {
  sm: "text-base font-semibold",
  md: "text-lg font-semibold",
  lg: "text-xl font-semibold",
  xl: "text-2xl font-bold",
} as const

const toneClasses = {
  default: "text-gray-900",
  muted: "text-gray-600",
  primary: "text-primary-700",
} as const

export type HeaderTextSize = keyof typeof sizeClasses
export type HeaderTextTone = keyof typeof toneClasses

export type HeaderTextProps<T extends ElementType = "h2"> = {
  as?: T
  size?: HeaderTextSize
  tone?: HeaderTextTone
  className?: string
} & ComponentPropsWithoutRef<T>

export function HeaderText<T extends ElementType = "h2">({
  as,
  size = "md",
  tone = "default",
  className,
  children,
  ...props
}: HeaderTextProps<T>) {
  const Component = as ?? ("h2" as ElementType)

  return (
    <Component
      className={cn(sizeClasses[size], toneClasses[tone], className)}
      {...props}
    >
      {children}
    </Component>
  )
}
