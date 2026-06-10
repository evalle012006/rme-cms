import { useCallback, useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { toast } from "sonner"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { Button } from "@/components/atoms/Button"
import { FormField } from "@/components/atoms/FormField"
import { ImageDropzone } from "@/components/atoms/ImageDropzone"
import { FormControl } from "@/components/atoms/FormControl"
import { Card } from "@/components/ui/card"
import { ratingsAPI, type Rating } from "@/api/ratings/ratingsAPIHandler"
import { isValidationError } from "@/api/apiService"
import { ROUTES } from "@/constants/routes"
import { RatingEditSkeleton } from "@/pages/ratings/components/RatingEditSkeleton"

const ratingSchema = z.object({
  name: z.string().min(1, "Name is required"),
  description: z.string().min(1, "Description is required"),
})

type RatingFormData = z.infer<typeof ratingSchema>

function decodeHtml(text: string): string {
  const el = document.createElement("textarea")
  el.innerHTML = text
  return el.value
}

function toFormValues(rating: Rating): RatingFormData {
  return {
    name: decodeHtml(rating.name),
    description: rating.description ? decodeHtml(rating.description) : "",
  }
}

export default function RatingEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const ratingId = Number(idParam)

  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [original, setOriginal] = useState<Rating | null>(null)

  const [ratingIcon, setRatingIcon] = useState<File | null>(null)
  const [existingIconUrl, setExistingIconUrl] = useState<string | null>(null)
  const [iconCleared, setIconCleared] = useState(false)
  const [ratingIconError, setRatingIconError] = useState<string | null>(null)

  const breadcrumb = [
    { label: "Home", to: ROUTES.root },
    { label: "Rating", to: ROUTES.rating.settings },
    { label: "Edit" },
  ]

  const {
    register,
    handleSubmit,
    reset,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<RatingFormData>({
    resolver: zodResolver(ratingSchema),
    defaultValues: { name: "", description: "" },
  })

  const applyRating = useCallback(
    (rating: Rating) => {
      setOriginal(rating)
      reset(toFormValues(rating))
      setExistingIconUrl(rating.full_ratingconf_icon)
      setRatingIcon(null)
      setIconCleared(false)
      setRatingIconError(null)
    },
    [reset]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(ratingId)) {
      toast.error("Invalid rating id")
      navigate(ROUTES.rating.settings)
      return
    }

    let mounted = true
    setLoading(true)

    ratingsAPI
      .getById(ratingId)
      .then((res) => {
        if (!mounted) return
        if (!res.success || !res.data) {
          toast.error("Rating not found")
          navigate(ROUTES.rating.settings)
          return
        }
        applyRating(res.data)
      })
      .catch(() => {
        if (mounted) navigate(ROUTES.rating.settings)
      })
      .finally(() => mounted && setLoading(false))

    return () => {
      mounted = false
    }
  }, [idParam, ratingId, navigate, applyRating])

  const hasIcon =
    !!ratingIcon || (!!existingIconUrl && !iconCleared)

  const visibleIconError = ratingIconError

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof RatingFormData)[] = ["name", "description"]
    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: "server", message })
      }
    })

    const iconMessage = apiErrors.rating_icon?.[0]
    if (iconMessage) {
      setRatingIconError(iconMessage)
    }
  }

  const validateIcon = useCallback(() => {
    if (!hasIcon) {
      setRatingIconError("Rating icon is required")
      return false
    }
    setRatingIconError(null)
    return true
  }, [hasIcon])

  const onSubmit = async (data: RatingFormData) => {
    if (!original) return
    clearErrors()
    if (!validateIcon()) return

    setSubmitting(true)
    try {
      await ratingsAPI.update(
        {
          id: original.id,
          name: data.name,
          description: data.description,
          ...(ratingIcon ? { rating_icon: ratingIcon } : {}),
        },
        {
          successMessage: "Rating updated successfully",
          customErrorMessage: "Failed to update rating",
        }
      )
      navigate(ROUTES.rating.settings)
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

  const onInvalid = () => {
    validateIcon()
  }

  const fieldsDisabled = loading

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          {loading ? (
            <RatingEditSkeleton />
          ) : (
            <>
              <FormControl
                layout="row"
                label="Name"
                placeholder="Name"
                disabled={fieldsDisabled}
                error={errors.name?.message}
                {...register("name")}
              />

              <FormControl
                variant="textarea"
                layout="row"
                label="Description"
                placeholder="Description"
                disabled={fieldsDisabled}
                error={errors.description?.message}
                {...register("description")}
              />

              <FormField
                layout="row"
                label="Rating Icon"
                error={visibleIconError ?? undefined}
              >
                <ImageDropzone
                  layout="banner"
                  value={ratingIcon}
                  existingImageUrl={iconCleared ? null : existingIconUrl}
                  disabled={fieldsDisabled}
                  onChange={(file) => {
                    setRatingIcon(file)
                    if (file) {
                      setRatingIconError(null)
                      setIconCleared(false)
                    }
                  }}
                  onClearExisting={() => {
                    setExistingIconUrl(null)
                    setIconCleared(true)
                  }}
                  error={visibleIconError ?? undefined}
                />
              </FormField>

              <div className="flex flex-wrap items-center gap-3 px-6 pb-6">
                <Button
                  type="submit"
                  color="primary"
                  disabled={submitting}
                  className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {submitting ? "Updating…" : "Update"}
                </Button>
                <Button
                  type="button"
                  color="destructive"
                  onClick={() => navigate(ROUTES.rating.settings)}
                  disabled={submitting}
                  className="px-6 py-3"
                >
                  Cancel
                </Button>
              </div>
            </>
          )}
        </Card>
      </form>
    </div>
  )
}
