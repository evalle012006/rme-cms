import { useCallback, useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { toast } from 'sonner'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { FormField } from '@/components/atoms/FormField'
import { ImageDropzone } from '@/components/atoms/ImageDropzone'
import { FormControl } from '@/components/atoms/FormControl'
import { Card } from '@/components/ui/card'
import {
  blogCategoriesAPI,
  type BlogCategory,
} from '@/api/blogCategories/blogCategoriesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { ROUTES } from '@/constants/routes'
import { BlogCategoryEditSkeleton } from '@/pages/blogCategories/components/BlogCategoryEditSkeleton'

const LIST_PATH = ROUTES.blogs.categories.list

const blogCategorySchema = z.object({
  name: z.string().min(1, 'Name is required'),
  seo_title: z.string(),
  seo_description: z.string(),
  seo_keywords: z.string(),
})

type BlogCategoryFormData = z.infer<typeof blogCategorySchema>

function toFormValues(category: BlogCategory): BlogCategoryFormData {
  return {
    name: category.name,
    seo_title: category.seo_title ?? '',
    seo_description: category.seo_description ?? '',
    seo_keywords: category.seo_keywords ?? '',
  }
}

export default function BlogCategoryEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const categoryId = Number(idParam)

  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [original, setOriginal] = useState<BlogCategory | null>(null)

  const [image, setImage] = useState<File | null>(null)
  const [existingImageUrl, setExistingImageUrl] = useState<string | null>(null)
  const [imageCleared, setImageCleared] = useState(false)
  const [imageError, setImageError] = useState<string | null>(null)

  const breadcrumb = [
    { label: 'Home', to: ROUTES.root },
    { label: 'Blog Category', to: LIST_PATH },
    { label: 'Edit' },
  ]

  const {
    register,
    handleSubmit,
    reset,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<BlogCategoryFormData>({
    resolver: zodResolver(blogCategorySchema),
    defaultValues: {
      name: '',
      seo_title: '',
      seo_description: '',
      seo_keywords: '',
    },
  })

  const applyCategory = useCallback(
    (category: BlogCategory) => {
      setOriginal(category)
      reset(toFormValues(category))
      setExistingImageUrl(category.full_blog_category_image)
      setImage(null)
      setImageCleared(false)
      setImageError(null)
    },
    [reset]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(categoryId)) {
      toast.error('Invalid blog category id')
      navigate(LIST_PATH)
      return
    }

    let mounted = true
    setLoading(true)

    blogCategoriesAPI
      .getById(categoryId, { customErrorMessage: 'Blog category not found' })
      .then((category) => {
        if (!mounted) return
        applyCategory(category)
      })
      .catch(() => {
        if (mounted) navigate(LIST_PATH)
      })
      .finally(() => mounted && setLoading(false))

    return () => {
      mounted = false
    }
  }, [idParam, categoryId, navigate, applyCategory])

  const hasImage =
    !!image || (!!existingImageUrl && !imageCleared) || (!!original?.image && !imageCleared)

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof BlogCategoryFormData)[] = [
      'name',
      'seo_title',
      'seo_description',
      'seo_keywords',
    ]

    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: 'server', message })
      }
    })

    const imageMessage = apiErrors.image?.[0]
    if (imageMessage) {
      setImageError(imageMessage)
    }
  }

  const validateImage = useCallback(() => {
    if (!hasImage) {
      setImageError('Image is required')
      return false
    }
    setImageError(null)
    return true
  }, [hasImage])

  const onSubmit = async (data: BlogCategoryFormData) => {
    if (!original) return
    clearErrors()
    if (!validateImage()) return

    const imagePayload = image ?? (imageCleared ? null : original.image)
    if (!imagePayload) return

    setSubmitting(true)
    try {
      await blogCategoriesAPI.update(
        {
          id: original.id,
          name: data.name,
          seo_title: data.seo_title,
          seo_description: data.seo_description,
          seo_keywords: data.seo_keywords,
          image: imagePayload,
        },
        {
          successMessage: 'Blog category updated successfully',
          customErrorMessage: 'Failed to update blog category',
        }
      )
      navigate(LIST_PATH)
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
    validateImage()
  }

  const fieldsDisabled = loading || submitting

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
            <BlogCategoryEditSkeleton />
          ) : (
            <>
              <FormField layout="row" label="Image" error={imageError ?? undefined}>
                <ImageDropzone
                  layout="banner"
                  value={image}
                  existingImageUrl={imageCleared ? null : existingImageUrl}
                  disabled={fieldsDisabled}
                  onChange={(file) => {
                    setImage(file)
                    if (file) {
                      setImageError(null)
                      setImageCleared(false)
                    }
                  }}
                  onClearExisting={() => {
                    setExistingImageUrl(null)
                    setImageCleared(true)
                  }}
                  error={imageError ?? undefined}
                />
              </FormField>

              <div className="grid grid-cols-1 md:grid-cols-2">
                <FormControl
                  layout="row"
                  label="Name"
                  placeholder="Name"
                  disabled={fieldsDisabled}
                  error={errors.name?.message}
                  fieldClassName="md:border-r md:border-gray-100"
                  {...register('name')}
                />

                <FormControl
                  layout="row"
                  label="SEO Title"
                  placeholder="SEO title"
                  disabled={fieldsDisabled}
                  error={errors.seo_title?.message}
                  {...register('seo_title')}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2">
                <FormControl
                  layout="row"
                  label="SEO Description"
                  placeholder="SEO description"
                  disabled={fieldsDisabled}
                  error={errors.seo_description?.message}
                  fieldClassName="md:border-r md:border-gray-100"
                  {...register('seo_description')}
                />

                <FormControl
                  variant="textarea"
                  layout="row"
                  label="SEO Keywords"
                  placeholder="SEO keywords"
                  disabled={fieldsDisabled}
                  error={errors.seo_keywords?.message}
                  {...register('seo_keywords')}
                />
              </div>

              <div className="flex flex-wrap items-center gap-3 px-6 pb-6">
                <Button
                  type="submit"
                  color="primary"
                  disabled={submitting}
                  className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {submitting ? 'Updating…' : 'Update'}
                </Button>
                <Button
                  type="button"
                  color="destructive"
                  onClick={() => navigate(LIST_PATH)}
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
