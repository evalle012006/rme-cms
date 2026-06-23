import { useCallback, useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { toast } from 'sonner'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { FormField } from '@/components/atoms/FormField'
import { ImageDropzone } from '@/components/atoms/ImageDropzone'
import { FormControl } from '@/components/atoms/FormControl'
import { Card } from '@/components/ui/card'
import { DescriptionBox } from '@/pages/cms/components/DescriptionBox'
import { blogCategoriesAPI, type BlogCategory } from '@/api/blogCategories/blogCategoriesAPIHandler'
import { blogsAPI } from '@/api/blogs/blogsAPIHandler'
import { isValidationError } from '@/api/apiService'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'
import {
  BLOG_STATUS_OPTIONS,
  blogFormSchema,
  formatBlogDateForApi,
  isDescriptionEmpty,
  nowDatetimeLocal,
  type BlogFormData,
} from '@/pages/blogs/blogFormShared'

const LIST_PATH = ROUTES.blogs.list

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Blogs', to: LIST_PATH },
  { label: 'Add' },
]

export default function BlogAddPage() {
  const navigate = useNavigate()
  const userId = useAuthStore((state) => state.user?.id)

  const [categories, setCategories] = useState<BlogCategory[]>([])
  const [categoriesLoading, setCategoriesLoading] = useState(true)

  const [featureImage, setFeatureImage] = useState<File | null>(null)
  const [featureImageError, setFeatureImageError] = useState<string | null>(null)
  const [description, setDescription] = useState('')
  const [descriptionError, setDescriptionError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<BlogFormData>({
    resolver: zodResolver(blogFormSchema),
    defaultValues: {
      title: '',
      blog_date: nowDatetimeLocal(),
      author_name: '',
      status: '0',
      seo_title: '',
      seo_description: '',
      seo_keywords: '',
      category_id: '',
    },
  })

  useEffect(() => {
    let mounted = true
    setCategoriesLoading(true)

    blogCategoriesAPI
      .getAll()
      .then((list) => {
        if (!mounted) return
        setCategories(list)
      })
      .catch(() => {})
      .finally(() => mounted && setCategoriesLoading(false))

    return () => {
      mounted = false
    }
  }, [])

  const handleCancel = useCallback(() => {
    navigate(LIST_PATH)
  }, [navigate])

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof BlogFormData)[] = [
      'title',
      'blog_date',
      'author_name',
      'status',
      'seo_title',
      'seo_description',
      'seo_keywords',
      'category_id',
    ]

    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: 'server', message })
      }
    })

    const imageMessage = apiErrors.feature_image?.[0]
    if (imageMessage) {
      setFeatureImageError(imageMessage)
    }

    const descriptionMessage = apiErrors.description?.[0]
    if (descriptionMessage) {
      setDescriptionError(descriptionMessage)
    }
  }

  const validateFeatureImage = useCallback(() => {
    if (!featureImage) {
      setFeatureImageError('Feature image is required')
      return false
    }
    setFeatureImageError(null)
    return true
  }, [featureImage])

  const validateDescription = useCallback(() => {
    if (isDescriptionEmpty(description)) {
      setDescriptionError('Description is required')
      return false
    }
    setDescriptionError(null)
    return true
  }, [description])

  const onSubmit = async (data: BlogFormData) => {
    clearErrors()
    const imageValid = validateFeatureImage()
    const descriptionValid = validateDescription()
    if (!imageValid || !descriptionValid || !featureImage) return

    if (!userId) {
      toast.error('Unable to determine current user')
      return
    }

    setSubmitting(true)
    try {
      await blogsAPI.create(
        {
          title: data.title,
          feature_image: featureImage,
          description,
          blog_date: formatBlogDateForApi(data.blog_date),
          status: data.status,
          seo_title: data.seo_title,
          seo_description: data.seo_description,
          seo_keywords: data.seo_keywords,
          category_id: Number(data.category_id),
          author_name: data.author_name,
          created_by: userId,
        },
        {
          successMessage: 'Blog created successfully',
          customErrorMessage: 'Failed to create blog',
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
    validateFeatureImage()
    validateDescription()
  }

  const fieldsDisabled = submitting || categoriesLoading

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          <FormField layout="row" label="Feature Image" error={featureImageError ?? undefined}>
            <ImageDropzone
              layout="banner"
              value={featureImage}
              onChange={(file) => {
                setFeatureImage(file)
                if (file) setFeatureImageError(null)
              }}
              disabled={fieldsDisabled}
              error={featureImageError ?? undefined}
            />
          </FormField>

          <FormField layout="row" label="Description" error={descriptionError ?? undefined}>
            <DescriptionBox
              label=""
              value={description}
              onChange={(html) => {
                setDescription(html)
                if (!isDescriptionEmpty(html)) setDescriptionError(null)
              }}
              disabled={fieldsDisabled}
              className="[&>p:first-child]:hidden"
            />
          </FormField>

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              layout="row"
              label="Title"
              placeholder="Title"
              disabled={fieldsDisabled}
              error={errors.title?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('title')}
            />

            <FormControl
              layout="row"
              label="Date"
              type="datetime-local"
              disabled={fieldsDisabled}
              error={errors.blog_date?.message}
              {...register('blog_date')}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              layout="row"
              label="Author Name"
              placeholder="Author name"
              disabled={fieldsDisabled}
              error={errors.author_name?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('author_name')}
            />

            <FormControl
              variant="select"
              layout="row"
              label="Status"
              disabled={fieldsDisabled}
              error={errors.status?.message}
              {...register('status')}
            >
              {BLOG_STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </FormControl>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2">
            <FormControl
              layout="row"
              label="SEO Title"
              placeholder="SEO title"
              disabled={fieldsDisabled}
              error={errors.seo_title?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('seo_title')}
            />

            <FormControl
              layout="row"
              label="SEO Description"
              placeholder="SEO description"
              disabled={fieldsDisabled}
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
              disabled={fieldsDisabled}
              error={errors.seo_keywords?.message}
              fieldClassName="md:border-r md:border-gray-100"
              {...register('seo_keywords')}
            />

            <FormControl
              variant="select"
              layout="row"
              label="Blog Category"
              disabled={fieldsDisabled}
              error={errors.category_id?.message}
              {...register('category_id')}
            >
              <option value="" disabled>
                Select category
              </option>
              {categories.map((category) => (
                <option key={category.id} value={String(category.id)}>
                  {category.name}
                </option>
              ))}
            </FormControl>
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
