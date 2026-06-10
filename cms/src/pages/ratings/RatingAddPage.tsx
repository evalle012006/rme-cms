import { useCallback, useState } from "react"
import { useNavigate } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { Button } from "@/components/atoms/Button"
import { FormField } from "@/components/atoms/FormField"
import { ImageDropzone } from "@/components/atoms/ImageDropzone"
import { FormControl } from "@/components/atoms/FormControl"
import { Card } from "@/components/ui/card"
import { ratingsAPI } from "@/api/ratings/ratingsAPIHandler"
import { isValidationError } from "@/api/apiService"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Rating", to: ROUTES.rating.settings },
  { label: "Add" },
]

const ratingSchema = z.object({
  name: z.string().min(1, "Name is required"),
  description: z.string().min(1, "Description is required"),
})

type RatingFormData = z.infer<typeof ratingSchema>

export default function RatingAddPage() {
  const navigate = useNavigate()
  const [ratingIcon, setRatingIcon] = useState<File | null>(null)
  const [ratingIconError, setRatingIconError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const handleCancel = useCallback(() => navigate(ROUTES.rating.settings), [navigate])

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<RatingFormData>({
    resolver: zodResolver(ratingSchema),
    defaultValues: {
      name: "",
      description: "",
    },
  })

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
    if (!ratingIcon) {
      setRatingIconError("Rating icon is required")
      return false
    }
    setRatingIconError(null)
    return true
  }, [ratingIcon])

  const handleIconChange = useCallback((file: File | null) => {
    setRatingIcon(file)
    if (file) {
      setRatingIconError(null)
    }
  }, [])

  const onSubmit = async (data: RatingFormData) => {
    clearErrors()
    if (!validateIcon() || !ratingIcon) return
    setSubmitting(true)
    try {
      await ratingsAPI.create(
        {
          name: data.name,
          description: data.description,
          rating_icon: ratingIcon,
        },
        {
          successMessage: "Rating created successfully",
          customErrorMessage: "Failed to create rating",
        }
      )
      handleCancel()
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

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          <FormControl
            layout="row"
            label="Name"
            placeholder="Name"
            error={errors.name?.message}
            {...register("name")}
          />

          <FormControl
            variant="textarea"
            layout="row"
            label="Description"
            placeholder="Description"
            error={errors.description?.message}
            {...register("description")}
          />

          <FormField layout="row" label="Rating Icon" error={visibleIconError ?? undefined}>
            <ImageDropzone
              layout="banner"
              value={ratingIcon}
              onChange={handleIconChange}
              disabled={submitting}
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
              {submitting ? "Creating…" : "Create"}
            </Button>
            <Button
              type="button"
              color="destructive"
              onClick={handleCancel}
              className="px-6 py-3"
            >
              Cancel
            </Button>
          </div>
        </Card>
      </form>
    </div>
  )
}
