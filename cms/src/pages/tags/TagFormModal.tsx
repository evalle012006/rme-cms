import { useEffect, useState } from "react"
import { useForm, Controller } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

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
import { FormControl } from "@/components/atoms/FormControl"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import { tagsAPI } from "@/api/tags/tagsAPIHandler"
import { isValidationError } from "@/api/apiService"

const TAG_TYPE_OPTIONS = [
  { value: "constructive", label: "Constructive" },
  { value: "positive", label: "Positive" },
] 

const tagSchema = z.object({
  name: z.string().min(1, "Tag name is required"),
  type: z.string().min(1, "Tag type is required"),
  is_active: z.boolean(),
})

type TagFormData = z.infer<typeof tagSchema>

const DEFAULT_VALUES: TagFormData = {
  name: "",
  type: "",
  is_active: true,
}

export type TagFormModalProps = {
  open: boolean
  tagId: number | null
  onClose: () => void
  onSuccess: () => void
}

export function TagFormModal({
  open,
  tagId,
  onClose,
  onSuccess,
}: TagFormModalProps) {
  const isEdit = tagId !== null
  const [fetching, setFetching] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    control,
    reset,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<TagFormData>({
    resolver: zodResolver(tagSchema),
    defaultValues: DEFAULT_VALUES,
  })

  useEffect(() => {
    if (!open) return

    clearErrors()

    if (!isEdit) {
      reset(DEFAULT_VALUES)
      return
    }

    let mounted = true
    setFetching(true)

    tagsAPI
      .getById(tagId)
      .then((tag) => {
        if (!mounted) return
        reset({
          name: tag.name,
          type: tag.type,
          is_active: tag.is_active,
        })
      })
      .catch(() => {
        if (mounted) onClose()
      })
      .finally(() => mounted && setFetching(false))

    return () => {
      mounted = false
    }
  }, [open, isEdit, tagId, reset, onClose, clearErrors])

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof TagFormData)[] = ["name", "type", "is_active"]
    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: "server", message })
      }
    })
  }

  const onSubmit = async (data: TagFormData) => {
    clearErrors()
    setSubmitting(true)
    try {
      if (isEdit) {
        await tagsAPI.update(
          {
            id: tagId,
            name: data.name,
            type: data.type,
            is_active: data.is_active,
          },
          {
            successMessage: "Tag updated successfully",
            customErrorMessage: "Failed to update tag",
          }
        )
      } else {
        await tagsAPI.add(
          {
            name: data.name,
            type: data.type,
            is_active: data.is_active,
          },
          {
            successMessage: "Tag added successfully",
            customErrorMessage: "Failed to add tag",
          }
        )
      }
      onSuccess()
      onClose()
    } catch (err) {
      // 422 validation: render inline field errors. Everything else has
      // already been toasted by the API wrapper / global interceptor.
      if (isValidationError(err)) {
        applyServerErrors(err.response.data.errors)
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={(next) => !next && onClose()}>
      <DialogPortal>
        <DialogBackdrop />
        <DialogPopup className="max-w-lg">
          <DialogHeader className="border-b border-gray-200 pb-4">
            <DialogTitle>{isEdit ? "Edit Tag" : "Add Tag"}</DialogTitle>
          </DialogHeader>

          <DialogContent className="py-5">
            {fetching ? (
              <p className="py-8 text-center text-sm text-gray-500">Loading…</p>
            ) : (
              <form
                id="tag-form"
                onSubmit={handleSubmit(onSubmit)}
                className="space-y-5"
                noValidate
              >
                <FormControl
                  label="Tag Name"
                  placeholder="Tag name"
                  error={errors.name?.message}
                  {...register("name")}
                />

                <FormControl
                  variant="select"
                  label="Tag Type"
                  error={errors.type?.message}
                  {...register("type")}
                >
                  <option value="" disabled>
                    Select type
                  </option>
                  {TAG_TYPE_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </FormControl>

                <Controller
                  name="is_active"
                  control={control}
                  render={({ field }) => (
                    <div className="flex items-center justify-between">
                      <Label htmlFor="tag-is-active">Is Active?</Label>
                      <Switch
                        id="tag-is-active"
                        checked={field.value}
                        onCheckedChange={field.onChange}
                      />
                    </div>
                  )}
                />

                <Button
                  type="submit"
                  color="primary"
                  disabled={submitting}
                  className="px-6 py-2.5 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {submitting
                    ? isEdit
                      ? "Updating…"
                      : "Adding…"
                    : isEdit
                      ? "Update"
                      : "Add Tag"}
                </Button>
              </form>
            )}
          </DialogContent>

          <DialogFooter className="border-t border-gray-200">
            <Button
              type="button"
              color="ghost"
              onClick={onClose}
              disabled={submitting}
              className="border border-gray-300 px-6 py-2.5"
            >
              Close
            </Button>
          </DialogFooter>
        </DialogPopup>
      </DialogPortal>
    </Dialog>
  )
}
