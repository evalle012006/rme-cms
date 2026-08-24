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
import { categoriesAPI } from '@/api/property-settings/categoriesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { ROUTES } from '@/constants/routes'

const LIST_PATH = ROUTES.propertySettings.categories.list

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Project Category', to: LIST_PATH },
  { label: 'Add' },
]

const categorySchema = z.object({
  name: z.string().min(1, 'Name is required'),
  description: z.string().min(1, 'Description is required'),
  question: z.string().min(1, 'Question is required'),
  seo_title: z.string().min(1, 'SEO title is required'),
  seo_description: z.string().min(1, 'SEO description is required'),
  seo_keywords: z.string().min(1, 'SEO keywords is required'),
})

type CategoryFormData = z.infer<typeof categorySchema>

export default function CategoryAddPage() {
  const navigate = useNavigate()
  const [catIcon, setCatIcon] = useState<File | null>(null)
  const [catIconError, setCatIconError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<CategoryFormData>({
    resolver: zodResolver(categorySchema),
    defaultValues: {
      name: '',
      description: '',
      question: '',
      seo_title: '',
      seo_description: '',
      seo_keywords: '',
    },
  })

  const handleCancel = useCallback(() => {
    navigate(LIST_PATH)
  }, [navigate])

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof CategoryFormData)[] = [
      'name',
      'description',
      'question',
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
      setCatIconError(imageMessage)
    }
  }

  const validateIcon = useCallback(() => {
    if (!catIcon) {
      setCatIconError('Cat icon is required')
      return false
    }
    setCatIconError(null)
    return true
  }, [catIcon])

  const onSubmit = async (data: CategoryFormData) => {
    clearErrors()
    if (!validateIcon() || !catIcon) return

    setSubmitting(true)
    try {
      await categoriesAPI.create(
        {
          name: data.name,
          description: data.description,
          question: data.question,
          seo_title: data.seo_title,
          seo_description: data.seo_description,
          seo_keywords: data.seo_keywords,
          image: catIcon,
        },
        {
          successMessage: 'Project category created successfully',
          customErrorMessage: 'Failed to create project category',
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
          <FormField layout="row" label="Cat Icon" error={catIconError ?? undefined}>
            <ImageDropzone
              layout="banner"
              value={catIcon}
              onChange={(file) => {
                setCatIcon(file)
                if (file) setCatIconError(null)
              }}
              disabled={submitting}
              error={catIconError ?? undefined}
            />
          </FormField>

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              variant="textarea"
              layout="row"
              label="Description"
              placeholder="Description"
              disabled={submitting}
              error={errors.description?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('description')}
            />

            <FormControl
              layout="row"
              label="Name"
              placeholder="Name"
              disabled={submitting}
              error={errors.name?.message}
              {...register('name')}
            />
          </div>

          <FormControl
            layout="row"
            label="Question"
            placeholder="Question"
            disabled={submitting}
            error={errors.question?.message}
            {...register('question')}
          />

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              layout="row"
              label="SEO Title"
              placeholder="SEO title"
              disabled={submitting}
              error={errors.seo_title?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('seo_title')}
            />

            <FormControl
              layout="row"
              label="SEO Description"
              placeholder="SEO description"
              disabled={submitting}
              error={errors.seo_description?.message}
              {...register('seo_description')}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              variant="textarea"
              layout="row"
              label="SEO Keywords"
              placeholder="SEO keywords"
              disabled={submitting}
              error={errors.seo_keywords?.message}
              fieldClassName="md:border-r md:border-gray-100"
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
              {submitting ? 'Creating…' : 'Create'}
            </Button>
            <Button
              type="button"
              color="destructive"
              onClick={handleCancel}
              disabled={submitting}
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
