import { ImageOff, X } from "lucide-react"
import {
  Dialog,
  DialogBackdrop,
  DialogClose,
  DialogPopup,
  DialogPortal,
} from "@/components/ui/dialog"
import { imgUrl } from "@/lib/utils"
import type { Advertise } from "@/api/advertises/advertisesAPIHandler"

// ── Advertise detail modal ───────────────────────────────────────────────────
// Two-column layout: image on the left, heading + body text on the right.
// The host owns the `open`/`advertise` state.

export interface ViewAdvertiseDialogProps {
  open: boolean
  advertise: Advertise | null
  onOpenChange: (open: boolean) => void
}

export function ViewAdvertiseDialog({
  open,
  advertise,
  onOpenChange,
}: ViewAdvertiseDialogProps) {
  // Prefer the backend's full URL accessor; fall back to resolving the raw path.
  const imageSrc = advertise?.full_image_path || imgUrl(advertise?.image)

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-2xl p-0">
          <DialogClose
            className="absolute top-3 right-3 z-10 rounded-md p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
            aria-label="Close"
          >
            <X size={18} />
          </DialogClose>

          <div className="grid grid-cols-1 gap-0 sm:grid-cols-2">
            <div className="flex items-center justify-center bg-gray-50 p-4 sm:rounded-l-card">
              {imageSrc ? (
                <img
                  src={imageSrc}
                  alt={advertise?.title ?? "Advertise"}
                  className="max-h-64 w-full rounded-md object-contain"
                />
              ) : (
                <div className="flex h-48 w-full flex-col items-center justify-center gap-2 text-gray-300">
                  <ImageOff size={40} />
                  <span className="text-xs">No image</span>
                </div>
              )}
            </div>

            <div className="flex flex-col justify-center gap-2 p-6">
              <h2 className="text-xl font-semibold text-gray-900">
                {advertise?.title || "—"}
              </h2>
              <p className="text-sm leading-relaxed whitespace-pre-line text-gray-600">
                {advertise?.description || "No description provided."}
              </p>
            </div>
          </div>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
