import { useState } from "react"
import { Button } from "@/components/atoms/Button"
import {
  Dialog,
  DialogBackdrop,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogPopup,
  DialogPortal,
  DialogTitle,
} from "@/components/ui/dialog"
import { usersAPI, type User } from "@/api/users/usersAPIHandler"

export type DisplayNameChangeRequestModalProps = {
  user: User | null
  open: boolean
  onClose: () => void
  onSuccess: () => void
}

export function DisplayNameChangeRequestModal({
  user,
  open,
  onClose,
  onSuccess,
}: DisplayNameChangeRequestModalProps) {
  const [submitting, setSubmitting] = useState(false)

  const handleClose = () => {
    if (submitting) return
    onClose()
  }

  const handleStatus = async (status: 0 | 1) => {
    if (!user || submitting) return
    const displayName = user.requested_display_name?.trim()
    if (!displayName) return

    setSubmitting(true)
    try {
      await usersAPI.changeRequestStatus(
        {
          id: user.id,
          display_name: displayName,
          status,
        },
        {
          successMessage:
            status === 1
              ? "Request approved successfully"
              : "Request rejected successfully",
          customErrorMessage: "Failed to update change request",
        }
      )
      onSuccess()
      onClose()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setSubmitting(false)
    }
  }

  const canApprove = Boolean(user?.requested_display_name?.trim())

  return (
    <Dialog open={open} onOpenChange={(next) => !next && handleClose()}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-md">
          <DialogHeader className="border-b border-gray-200 pb-4">
            <DialogTitle>Display name change request</DialogTitle>
          </DialogHeader>

          <DialogContent className="space-y-4 py-5">
            <div className="space-y-1 text-sm text-gray-700">
              <p>
                <span className="font-semibold">Old display name</span>
                {": "}
                {user?.display_name || "—"}
              </p>
              <p>
                <span className="font-semibold">New display name</span>
                {": "}
                {user?.requested_display_name || "—"}
              </p>
            </div>
          </DialogContent>

          <DialogFooter className="border-t border-gray-200 !justify-end gap-2">
            <Button
              type="button"
              color="destructive"
              disabled={submitting}
              onClick={() => handleStatus(0)}
              className="px-6 py-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {submitting ? "Saving…" : "Reject"}
            </Button>
            <Button
              type="button"
              color="primary"
              disabled={submitting || !canApprove}
              onClick={() => handleStatus(1)}
              className="px-6 py-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {submitting ? "Saving…" : "Approve"}
            </Button>
          </DialogFooter>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
