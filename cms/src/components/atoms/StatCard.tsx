import { Card } from "@/components/ui/card"
import { cn } from "@/lib/utils"
import type { LucideIcon } from "lucide-react"
import type { ComponentProps } from "react"

export type StatCardProps = Omit<ComponentProps<typeof Card>, "children"> & {
  icon: LucideIcon
  count: number | string
  label: string
  iconClassName?: string
  countClassName?: string
}

export function StatCard({
  icon: Icon,
  count,
  label,
  iconClassName,
  countClassName,
  className,
  ...props
}: StatCardProps) {
  return (
    <Card
      className={cn(
        "app-border rounded-card flex items-center gap-4 p-4",
        className
      )}
      {...props}
    >
      <div
        className={cn(
          "flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-white",
          iconClassName
        )}
      >
        <Icon size={24} />
      </div>
      <div>
        <p className={cn("text-2xl font-bold", countClassName)}>{count}</p>
        <p className="text-xs font-bold uppercase tracking-wide text-gray-500">
          {label}
        </p>
      </div>
    </Card>
  )
}
