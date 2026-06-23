import { useCallback, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { FormField } from '@/components/atoms/FormField'
import { ImageDropzone } from '@/components/atoms/ImageDropzone'
import { FormControl } from '@/components/atoms/FormControl'
import { Card } from '@/components/ui/card'
import { blogCategoriesAPI } from '@/api/blogCategories/blogCategoriesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { ROUTES } from '@/constants/routes'

const LIST_PATH = ROUTES.blogs.categories.list

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Blog Category', to: LIST_PATH },
  { label: 'Add' },
]

const blogCategorySchema = z.object({
  name: z.string().min(1, 'Name is required'),
  seo_title: z.string(),
  seo_description: z.string(),
  seo_keywords: z.string(),
})

type BlogCategoryFormData = z.infer<typeof blogCategorySchema>

export default function BlogCategoryAddPage() {
  const navigate = useNavigate()
  const [image, setImage] = useState<File | null>(null)
  const [imageError, setImageError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
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

  const handleCancel = useCallback(() => {
    navigate(LIST_PATH)
  }, [navigate])

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
    if (!image) {
      setImageError('Image is required')
      return false
    }
    setImageError(null)
    return true
  }, [image])

  const handleImageChange = useCallback((file: File | null) => {
    setImage(file)
    if (file) {
      setImageError(null)
    }
  }, [])

  const onSubmit = async (data: BlogCategoryFormData) => {
    clearErrors()
    if (!validateImage() || !image) return

    setSubmitting(true)
    try {
      await blogCategoriesAPI.create(
        {
          name: data.name,
          seo_title: data.seo_title,
          seo_description: data.seo_description,
          seo_keywords: data.seo_keywords,
          image,
        },
        {
          successMessage: 'Blog category created successfully',
          customErrorMessage: 'Failed to create blog category',
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

  const fieldsDisabled = submitting

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          <FormField layout="row" label="Image" error={imageError ?? undefined}>
            <ImageDropzone
              layout="banner"
              value={image}
              onChange={handleImageChange}
              disabled={fieldsDisabled}
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
              disabled={fieldsDisabled}
              className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {submitting ? 'Creating…' : 'Create'}
            </Button>
            <Button
              type="button"
              color="destructive"
              onClick={handleCancel}
              disabled={fieldsDisabled}
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
