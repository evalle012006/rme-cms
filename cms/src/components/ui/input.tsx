import { cn } from "@/lib/utils"
import { forwardRef, type ComponentProps } from "react"

const Input = forwardRef<HTMLInputElement, ComponentProps<"input">>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        ref={ref}
        data-slot="input"
        className={cn(
          "w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition",
          "focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none",
          "disabled:pointer-events-none disabled:opacity-50",
          "aria-invalid:border-red-500 aria-invalid:ring-red-200",
          className
        )}
        {...props}
      />
    )
  }
)

Input.displayName = "Input"

export { Input }
