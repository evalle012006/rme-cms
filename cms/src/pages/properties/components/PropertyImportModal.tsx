import { useRef, useState } from 'react'
import { Button } from '@/components/atoms/Button'
import {
  Dialog,
  DialogBackdrop,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogPopup,
  DialogPortal,
  DialogTitle,
} from '@/components/ui/dialog'
import { propertiesAPI } from '@/api/properties/propertiesAPIHandler'
import { cn } from '@/lib/utils'
import templateCsv from '../../../../templates/rme-property-example.csv?raw'

const PROPERTY_IMPORT_TEMPLATE_FILENAME = 'rme-property-example.csv'

export type PropertyImportModalProps = {
  open: boolean
  onClose: () => void
  onSuccess: () => void
}

export function PropertyImportModal({
  open,
  onClose,
  onSuccess,
}: PropertyImportModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [selectedFile, setSelectedFile] = useState<File | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const resetState = () => {
    setSelectedFile(null)
    if (fileInputRef.current) {
      fileInputRef.current.value = ''
    }
  }

  const handleClose = () => {
    if (submitting) return
    resetState()
    onClose()
  }

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0] ?? null
    setSelectedFile(file)
  }

  const handleDownloadTemplate = () => {
    const blob = new Blob([templateCsv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = PROPERTY_IMPORT_TEMPLATE_FILENAME
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  }

  const handleImport = async () => {
    if (!selectedFile || submitting) return

    setSubmitting(true)
    try {
      await propertiesAPI.importFile(selectedFile, {
        successMessage: 'Properties imported successfully',
        customErrorMessage: 'Failed to import properties',
      })
      resetState()
      onSuccess()
      onClose()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={(next) => !next && handleClose()}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-lg">
          <DialogHeader className="border-b border-gray-200 pb-4">
            <DialogTitle>Import</DialogTitle>
          </DialogHeader>

          <DialogContent className="space-y-4 py-5">
            <input
              ref={fileInputRef}
              type="file"
              onChange={handleFileChange}
              disabled={submitting}
              className={cn(
                'block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700',
                'file:mr-4 file:rounded-md file:border-0 file:bg-gray-200 file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-gray-700',
                'disabled:cursor-not-allowed disabled:opacity-60'
              )}
            />

            <div className="flex flex-wrap items-center gap-4">
              <button
                type="button"
                onClick={handleDownloadTemplate}
                className="text-sm font-medium text-primary-500 hover:text-primary-600 hover:underline"
              >
                Download Import Template
              </button>

              <Button
                type="button"
                color="primary"
                disabled={!selectedFile || submitting}
                onClick={handleImport}
                className="px-6 py-2 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {submitting ? 'Importing…' : 'Import'}
              </Button>
            </div>
          </DialogContent>

          <DialogFooter className="border-t border-gray-200 !justify-end">
            <button
              type="button"
              onClick={handleClose}
              disabled={submitting}
              className={cn(
                'rounded-md bg-red-500 px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors',
                'hover:bg-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400 focus-visible:ring-offset-1',
                'disabled:cursor-not-allowed disabled:opacity-60'
              )}
            >
              Cancel
            </button>
          </DialogFooter>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
