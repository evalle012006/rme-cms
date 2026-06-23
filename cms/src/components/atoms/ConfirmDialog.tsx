import { useState, type ReactNode } from "react"
import { X } from "lucide-react"
import {
  Dialog,
  DialogBackdrop,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogPopup,
  DialogPortal,
  DialogTitle,
} from "@/components/ui/dialog"
import { cn } from "@/lib/utils"

// ── Reusable confirmation dialog ─────────────────────────────────────────────
// Renders the "Are you sure?" pattern used across destructive/state-change
// actions. The host owns the `open` state; `onConfirm` may be async — while
// it's pending the buttons are disabled to prevent double-submits.

export interface ConfirmDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  title?: string
  description?: ReactNode
  confirmLabel?: string
  cancelLabel?: string
  // Tone of the warning icon at the top of the dialog. Defaults to `danger`
  // (red), matching the legacy app's status-change modal.
  tone?: "danger" | "warning"
  onConfirm: () => void | Promise<void>
}

export function ConfirmDialog({
  open,
  onOpenChange,
  title = "Are you sure?",
  description,
  confirmLabel = "Yes",
  cancelLabel = "Cancel",
  tone = "danger",
  onConfirm,
}: ConfirmDialogProps) {
  const [submitting, setSubmitting] = useState(false)

  const handleConfirm = async () => {
    if (submitting) return
    try {
      setSubmitting(true)
      await onConfirm()
      onOpenChange(false)
    } finally {
      setSubmitting(false)
    }
  }

  const handleCancel = () => {
    if (submitting) return
    onOpenChange(false)
  }

  const iconRingClass =
    tone === "danger" ? "border-red-500 text-red-500" : "border-yellow-500 text-yellow-500"

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-sm">
          <DialogHeader className="items-center pt-8 text-center">
            <div
              className={cn(
                "mb-3 flex h-16 w-16 items-center justify-center rounded-full border-4",
                iconRingClass
              )}
              aria-hidden
            >
              <X size={32} strokeWidth={3} />
            </div>
            <DialogTitle className="text-center text-xl">{title}</DialogTitle>
          </DialogHeader>

          {description && (
            <DialogContent className="text-center">
              <DialogDescription className="text-center text-sm text-gray-600">
                {description}
              </DialogDescription>
            </DialogContent>
          )}

          <DialogFooter className="!flex-row !items-center !justify-center gap-3 border-t border-gray-200 pt-4">
            <button
              type="button"
              onClick={handleCancel}
              disabled={submitting}
              className={cn(
                "rounded-md bg-red-500 px-5 py-1.5 text-sm font-semibold text-white shadow-sm transition-colors",
                "hover:bg-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400 focus-visible:ring-offset-1",
                "disabled:cursor-not-allowed disabled:opacity-60"
              )}
            >
              {cancelLabel}
            </button>
            <button
              type="button"
              onClick={handleConfirm}
              disabled={submitting}
              className={cn(
                "rounded-md bg-emerald-500 px-5 py-1.5 text-sm font-semibold text-white shadow-sm transition-colors",
                "hover:bg-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-1",
                "disabled:cursor-not-allowed disabled:opacity-60"
              )}
            >
              {submitting ? "Please wait…" : confirmLabel}
            </button>
          </DialogFooter>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
