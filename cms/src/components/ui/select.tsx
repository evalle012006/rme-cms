import { cn } from "@/lib/utils"
import { forwardRef, type ComponentProps } from "react"

const Select = forwardRef<HTMLSelectElement, ComponentProps<"select">>(
  ({ className, ...props }, ref) => {
    return (
      <select
        ref={ref}
        data-slot="select"
        className={cn(
          "w-full rounded-card border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition",
          "focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none",
          "disabled:pointer-events-none disabled:opacity-50",
          className
        )}
        {...props}
      />
    )
  }
)

Select.displayName = "Select"

export { Select }
