import { cn } from "@/lib/utils"
import { ChevronDown } from "lucide-react"
import type { ComponentProps } from "react"

export type SelectDropdownOption = {
  value: string
  label: string
}

export type SelectDropdownProps = Omit<ComponentProps<"select">, "children"> & {
  options: SelectDropdownOption[]
  placeholder?: string
}

export function SelectDropdown({
  options,
  placeholder,
  className,
  ...props
}: SelectDropdownProps) {
  return (
    <div className="relative">
      <select
        className={cn(
          "w-full appearance-none rounded-card border border-gray-300 bg-white py-2.5 pr-9 pl-3.5 text-sm text-gray-700 transition",
          "focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none",
          "disabled:pointer-events-none disabled:opacity-50",
          className
        )}
        {...props}
      >
        {placeholder && <option value="">{placeholder}</option>}
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
      <ChevronDown
        size={16}
        className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"
      />
    </div>
  )
}
