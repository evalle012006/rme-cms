import { useEffect, useState } from "react"
import { Trash2 } from "lucide-react"

import { Button } from "@/components/atoms/Button"
import { FormControl } from "@/components/atoms/FormControl"
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
import { isValidationError } from "@/api/apiService"
import { reviewsAPI, type Review } from "@/api/reviews/reviewsAPIHandler"

// ── Delete review with reason ────────────────────────────────────────────────
// Deleting a review is a soft delete on the backend, but it is still the
// removal of someone's content, so the admin has to say why. The reason is
// stored on the row together with who deleted it and when.

const REASON_MAX_LENGTH = 1000

export interface DeleteReviewDialogProps {
  review: Review | null
  onClose: () => void
  onDeleted: () => void
}

export function DeleteReviewDialog({
  review,
  onClose,
  onDeleted,
}: DeleteReviewDialogProps) {
  const open = review !== null
  const [reason, setReason] = useState("")
  const [error, setError] = useState<string | undefined>(undefined)
  const [submitting, setSubmitting] = useState(false)

  // Start every delete from a clean slate so a reason typed for one review
  // never carries over to another.
  useEffect(() => {
    if (open) {
      setReason("")
      setError(undefined)
      setSubmitting(false)
    }
  }, [open, review?.id])

  const trimmed = reason.trim()
  const canSubmit = trimmed.length > 0 && !submitting

  const handleClose = () => {
    if (submitting) return
    onClose()
  }

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    if (!review) return

    if (!trimmed) {
      setError("A reason for deleting this review is required.")
      return
    }

    setSubmitting(true)
    setError(undefined)
    try {
      await reviewsAPI.delete(review.id, trimmed, {
        successMessage: "Review deleted successfully",
        customErrorMessage: "Failed to delete review",
      })
      onDeleted()
      onClose()
    } catch (err) {
      // 422 validation: show the server's reason error inline and keep the
      // dialog open. Anything else (e.g. 404 because the review was already
      // deleted elsewhere) has been toasted by the interceptor; close and
      // let the list refresh so the stale row disappears.
      if (isValidationError(err)) {
        setError(err.response.data.errors.reason?.[0])
      } else {
        onDeleted()
        onClose()
      }
    } finally {
      setSubmitting(false)
    }
  }

  const title = review?.title ? `"${review.title}"` : "this review"

  return (
    <Dialog open={open} onOpenChange={(next) => !next && handleClose()}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-lg">
          <DialogHeader className="border-b border-gray-200 pb-4">
            <div className="flex items-center gap-3">
              <div
                className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"
                aria-hidden
              >
                <Trash2 size={20} />
              </div>
              <div>
                <DialogTitle>Delete review?</DialogTitle>
                <DialogDescription className="mt-1 text-sm text-gray-600">
                  You are about to delete {title}. It will be removed from the
                  site and from ratings. Please record why.
                </DialogDescription>
              </div>
            </div>
          </DialogHeader>

          <DialogContent className="py-5">
            <form
              id="delete-review-form"
              onSubmit={handleSubmit}
              className="space-y-2"
              noValidate
            >
              <FormControl
                variant="textarea"
                label="Reason for deletion"
                required
                placeholder="e.g. Duplicate submission, abusive content, reviewer requested removal…"
                value={reason}
                maxLength={REASON_MAX_LENGTH}
                disabled={submitting}
                error={error}
                autoFocus
                onChange={(e) => {
                  setReason(e.target.value)
                  if (error) setError(undefined)
                }}
              />
              <p className="text-right text-xs text-gray-400">
                {trimmed.length}/{REASON_MAX_LENGTH}
              </p>
            </form>
          </DialogContent>

          <DialogFooter className="border-t border-gray-200">
            <Button
              type="button"
              color="ghost"
              onClick={handleClose}
              disabled={submitting}
              className="border border-gray-300 px-6 py-2.5"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              form="delete-review-form"
              color="destructive"
              disabled={!canSubmit}
              className="px-6 py-2.5 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {submitting ? "Deleting…" : "Delete review"}
            </Button>
          </DialogFooter>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
