import {
  Button as ShadcnButton,
  buttonVariants,
} from "@/components/ui/button"
import { cn } from "@/lib/utils"
import type { VariantProps } from "class-variance-authority"
import type { ComponentProps } from "react"

const colorClasses = {
  primary:"bg-primary-500 text-white hover:bg-primary-600 focus-visible:ring-primary-400",
  secondary: "bg-gray-200 text-gray-900 hover:bg-gray-300",
  destructive: "bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-400",
  ghost: "bg-transparent text-gray-700 hover:bg-gray-100",
} as const

export type ButtonColor = keyof typeof colorClasses

export type ButtonProps = ComponentProps<typeof ShadcnButton> &
  VariantProps<typeof buttonVariants> & {
    color?: ButtonColor
    className?: string
  }

export function Button({ className, color, variant, size, ...props }: ButtonProps) {
  return (
    <ShadcnButton
      variant={variant}
      size={size}
      className={cn(color && colorClasses[color], className)}
      {...props}
    />
  )
}
