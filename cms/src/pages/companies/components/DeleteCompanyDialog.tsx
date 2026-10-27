import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'

export type DeleteCompanyDialogProps = {
  open: boolean
  companyName?: string
  // Reviews attached to the company's projects. When > 0 the delete is
  // blocked: reviews are the site's content and must be moved to another
  // company/project first (the backend enforces the same rule with a 409).
  reviewsCount?: number
  loading?: boolean
  onOpenChange: (open: boolean) => void
  onConfirm: () => void
}

// Rendered as a single ConfirmDialog instance (rather than two alternating
// ones) so the popup doesn't swap content mid fade-out when the host clears
// its selected row on close.
export function DeleteCompanyDialog({
  open,
  companyName,
  reviewsCount = 0,
  onOpenChange,
  onConfirm,
}: Omit<DeleteCompanyDialogProps, 'loading'>) {
  const blocked = reviewsCount > 0
  const label = companyName ? `"${companyName}"` : 'this company'
  const noun = reviewsCount === 1 ? 'review' : 'reviews'

  return (
    <ConfirmDialog
      open={open}
      blocked={blocked}
      tone={blocked ? 'warning' : 'danger'}
      title={blocked ? 'Cannot delete company' : 'Delete company?'}
      description={
        blocked
          ? `${label} has ${reviewsCount} ${noun} attached. Move the reviews to another company or project first, then delete the company.`
          : `Are you sure you want to delete ${label}? This action cannot be undone.`
      }
      confirmLabel="Confirm"
      onOpenChange={onOpenChange}
      onConfirm={blocked ? undefined : onConfirm}
    />
  )
}
