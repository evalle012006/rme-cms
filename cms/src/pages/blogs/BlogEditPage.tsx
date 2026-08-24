import { useCallback, useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
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
import { blogsAPI, type Blog } from '@/api/blogs/blogsAPIHandler'
import { isValidationError } from '@/api/apiService'
import { ROUTES } from '@/constants/routes'
import {
  BLOG_STATUS_OPTIONS,
  blogFormSchema,
  formatBlogDateForApi,
  isDescriptionEmpty,
  toDatetimeLocalValue,
  type BlogFormData,
} from '@/pages/blogs/blogFormShared'
import { stashBlogPreview } from '@/pages/blogs/blogPreviewShared'
import { BlogEditSkeleton } from '@/pages/blogs/components/BlogEditSkeleton'
import { useAuthStore } from '@/store/authStore'
import { imgUrl } from '@/lib/utils'

const LIST_PATH = ROUTES.blogs.list

function toFormValues(blog: Blog): BlogFormData {
  return {
    title: blog.title,
    blog_date: toDatetimeLocalValue(blog.blog_date),
    author_name: blog.author_name ?? '',
    status: blog.status,
    seo_title: blog.seo_title ?? '',
    seo_description: blog.seo_description ?? '',
    seo_keywords: blog.seo_keywords ?? '',
    category_id: String(blog.category_id),
  }
}

export default function BlogEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const blogId = Number(idParam)

  const [loading, setLoading] = useState(true)
  const [categories, setCategories] = useState<BlogCategory[]>([])
  const [submitting, setSubmitting] = useState(false)
  const [original, setOriginal] = useState<Blog | null>(null)

  const [featureImage, setFeatureImage] = useState<File | null>(null)
  const [existingFeatureImageUrl, setExistingFeatureImageUrl] = useState<string | null>(null)
  const [featureImageCleared, setFeatureImageCleared] = useState(false)
  const [featureImageError, setFeatureImageError] = useState<string | null>(null)

  const [description, setDescription] = useState('')
  const [descriptionError, setDescriptionError] = useState<string | null>(null)

  const breadcrumb = [
    { label: 'Home', to: ROUTES.root },
    { label: 'Blogs', to: LIST_PATH },
    { label: 'Edit' },
  ]

  const {
    register,
    handleSubmit,
    reset,
    getValues,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<BlogFormData>({
    resolver: zodResolver(blogFormSchema),
    defaultValues: {
      title: '',
      blog_date: '',
      author_name: '',
      status: '0',
      seo_title: '',
      seo_description: '',
      seo_keywords: '',
      category_id: '',
    },
  })

  const applyBlog = useCallback(
    (blog: Blog) => {
      setOriginal(blog)
      reset(toFormValues(blog))
      setExistingFeatureImageUrl(blog.full_feature_image)
      setFeatureImage(null)
      setFeatureImageCleared(false)
      setFeatureImageError(null)
      setDescription(blog.description ?? '')
      setDescriptionError(null)
    },
    [reset]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(blogId)) {
      toast.error('Invalid blog id')
      navigate(LIST_PATH)
      return
    }

    let mounted = true
    setLoading(true)

    Promise.all([
      blogsAPI.getById(blogId, { customErrorMessage: 'Blog not found' }),
      blogCategoriesAPI.getAll(),
    ])
      .then(([blog, categoryList]) => {
        if (!mounted) return
        applyBlog(blog)
        setCategories(categoryList)
      })
      .catch(() => {
        if (mounted) navigate(LIST_PATH)
      })
      .finally(() => mounted && setLoading(false))

    return () => {
      mounted = false
    }
  }, [idParam, blogId, navigate, applyBlog])

  const hasFeatureImage =
    !!featureImage ||
    (!!existingFeatureImageUrl && !featureImageCleared) ||
    (!!original?.feature_image && !featureImageCleared)

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
    if (!hasFeatureImage) {
      setFeatureImageError('Feature image is required')
      return false
    }
    setFeatureImageError(null)
    return true
  }, [hasFeatureImage])

  const validateDescription = useCallback(() => {
    if (isDescriptionEmpty(description)) {
      setDescriptionError('Description is required')
      return false
    }
    setDescriptionError(null)
    return true
  }, [description])

  const onSubmit = async (data: BlogFormData) => {
    if (!original) return
    clearErrors()
    const imageValid = validateFeatureImage()
    const descriptionValid = validateDescription()
    if (!imageValid || !descriptionValid) return

    const featureImagePayload =
      featureImage ?? (featureImageCleared ? null : original.feature_image)
    if (!featureImagePayload) return

    setSubmitting(true)
    try {
      await blogsAPI.update(
        {
          id: original.id,
          title: data.title,
          feature_image: featureImagePayload,
          description,
          blog_date: formatBlogDateForApi(data.blog_date),
          status: data.status,
          seo_title: data.seo_title,
          seo_description: data.seo_description,
          seo_keywords: data.seo_keywords,
          category_id: Number(data.category_id),
          author_name: data.author_name,
        },
        {
          successMessage: 'Blog updated successfully',
          customErrorMessage: 'Failed to update blog',
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

  const handlePreview = useCallback(() => {
    const data = getValues()
    const category = categories.find((item) => String(item.id) === data.category_id)
    const featureImageUrl = featureImage
      ? URL.createObjectURL(featureImage)
      : featureImageCleared
        ? null
        : existingFeatureImageUrl
    const authUser = useAuthStore.getState().user
    const authorImageUrl =
      original?.user?.full_profile_image ??
      imgUrl(original?.user?.profile_image) ??
      imgUrl(authUser?.profile_image) ??
      imgUrl(authUser?.profile_thumb_image)

    stashBlogPreview({
      title: data.title,
      description,
      blog_date: data.blog_date,
      author_name: data.author_name,
      status: data.status,
      category_name: category?.name ?? '',
      feature_image_url: featureImageUrl,
      author_image_url: authorImageUrl,
      return_to: original ? ROUTES.blogs.edit(original.id) : LIST_PATH,
    })
    navigate(ROUTES.blogs.previewDraft)
  }, [
    categories,
    description,
    existingFeatureImageUrl,
    featureImage,
    featureImageCleared,
    getValues,
    navigate,
    original,
  ])

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
            <BlogEditSkeleton />
          ) : (
            <>
              <FormField
                layout="row"
                label="Feature Image"
                error={featureImageError ?? undefined}
              >
                <ImageDropzone
                  layout="banner"
                  value={featureImage}
                  existingImageUrl={featureImageCleared ? null : existingFeatureImageUrl}
                  disabled={fieldsDisabled}
                  onChange={(file) => {
                    setFeatureImage(file)
                    if (file) {
                      setFeatureImageError(null)
                      setFeatureImageCleared(false)
                    }
                  }}
                  onClearExisting={() => {
                    setExistingFeatureImageUrl(null)
                    setFeatureImageCleared(true)
                  }}
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
                  disabled={submitting}
                  className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {submitting ? 'Updating…' : 'Update'}
                </Button>
                <Button
                  type="button"
                  color="secondary"
                  onClick={handlePreview}
                  disabled={fieldsDisabled}
                  className="px-6 py-3"
                >
                  Preview
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
