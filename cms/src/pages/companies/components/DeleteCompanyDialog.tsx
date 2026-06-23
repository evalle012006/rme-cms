import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'

export type DeleteCompanyDialogProps = {
  open: boolean
  companyName?: string
  loading?: boolean
  onOpenChange: (open: boolean) => void
  onConfirm: () => void
}

export function DeleteCompanyDialog({
  open,
  companyName,
  onOpenChange,
  onConfirm,
}: Omit<DeleteCompanyDialogProps, 'loading'>) {
  return (
    <ConfirmDialog
      open={open}
      title="Delete company?"
      description={
        companyName
          ? `Are you sure you want to delete "${companyName}"? This action cannot be undone.`
          : 'Are you sure you want to delete this company? This action cannot be undone.'
      }
      confirmLabel="Confirm"
      onOpenChange={onOpenChange}
      onConfirm={onConfirm}
    />
  )
}
