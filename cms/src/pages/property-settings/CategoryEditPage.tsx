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

  categoriesAPI,

  type PropertyCategory,

} from '@/api/property-settings/categoriesAPIHandler'

import { isValidationError } from '@/api/apiService'

import { ROUTES } from '@/constants/routes'

import { CategoryEditSkeleton } from '@/pages/property-settings/components/CategoryEditSkeleton'



const LIST_PATH = ROUTES.propertySettings.categories.list



const categorySchema = z.object({

  name: z.string().min(1, 'Name is required'),

  description: z.string().min(1, 'Description is required'),

  question: z.string().min(1, 'Question is required'),

  seo_title: z.string().min(1, 'SEO title is required'),

  seo_description: z.string().min(1, 'SEO description is required'),

  seo_keywords: z.string().min(1, 'SEO keywords is required'),

})



type CategoryFormData = z.infer<typeof categorySchema>



function decodeHtml(text: string): string {

  const el = document.createElement('textarea')

  el.innerHTML = text

  return el.value

}



function toFormValues(category: PropertyCategory): CategoryFormData {

  return {

    name: decodeHtml(category.name),

    description: decodeHtml(category.description),

    question: decodeHtml(category.question),

    seo_title: decodeHtml(category.seo_title),

    seo_description: decodeHtml(category.seo_description),

    seo_keywords: decodeHtml(category.seo_keywords),

  }

}



export default function CategoryEditPage() {

  const navigate = useNavigate()

  const { id: idParam } = useParams<{ id: string }>()

  const categoryId = Number(idParam)



  const [loading, setLoading] = useState(true)

  const [submitting, setSubmitting] = useState(false)

  const [original, setOriginal] = useState<PropertyCategory | null>(null)



  const [catIcon, setCatIcon] = useState<File | null>(null)

  const [existingIconUrl, setExistingIconUrl] = useState<string | null>(null)

  const [iconCleared, setIconCleared] = useState(false)

  const [catIconError, setCatIconError] = useState<string | null>(null)



  const breadcrumb = [

    { label: 'Home', to: ROUTES.root },

    { label: 'Project Category', to: ROUTES.propertySettings.categories.list },

    { label: 'Edit' },

  ]



  const {

    register,

    handleSubmit,

    reset,

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



  const applyCategory = useCallback(

    (category: PropertyCategory) => {

      setOriginal(category)

      reset(toFormValues(category))

      setExistingIconUrl(category.full_cat_image)

      setCatIcon(null)

      setIconCleared(false)

      setCatIconError(null)

    },

    [reset]

  )



  useEffect(() => {

    if (!idParam || Number.isNaN(categoryId)) {

      toast.error('Invalid category id')

      navigate(LIST_PATH)

      return

    }



    let mounted = true

    setLoading(true)



    categoriesAPI

      .getById(categoryId)

      .then((res) => {

        if (!mounted) return

        if (!res.success || !res.data) {

          toast.error('Project category not found')

          navigate(LIST_PATH)

          return

        }

        applyCategory(res.data)

      })

      .catch(() => {

        if (mounted) navigate(LIST_PATH)

      })

      .finally(() => mounted && setLoading(false))



    return () => {

      mounted = false

    }

  }, [idParam, categoryId, navigate, applyCategory])



  const hasIcon = !!catIcon || (!!existingIconUrl && !iconCleared)



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

    if (!hasIcon) {

      setCatIconError('Cat icon is required')

      return false

    }

    setCatIconError(null)

    return true

  }, [hasIcon])



  const onSubmit = async (data: CategoryFormData) => {

    if (!original) return

    clearErrors()

    if (!validateIcon()) return



    const imagePayload = catIcon ?? (iconCleared ? null : original.image)

    if (!imagePayload) return



    setSubmitting(true)

    try {

      await categoriesAPI.update(

        {

          id: original.id,

          name: data.name,

          description: data.description,

          question: data.question,

          seo_title: data.seo_title,

          seo_description: data.seo_description,

          seo_keywords: data.seo_keywords,

          image: imagePayload,

        },

        {

          successMessage: 'Project category updated successfully',

          customErrorMessage: 'Failed to update project category',

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

            <CategoryEditSkeleton />

          ) : (

            <>

              <FormField layout="row" label="Cat Icon" error={catIconError ?? undefined}>

                <ImageDropzone

                  layout="banner"

                  value={catIcon}

                  existingImageUrl={iconCleared ? null : existingIconUrl}

                  disabled={fieldsDisabled}

                  onChange={(file) => {

                    setCatIcon(file)

                    if (file) {

                      setCatIconError(null)

                      setIconCleared(false)

                    }

                  }}

                  onClearExisting={() => {

                    setExistingIconUrl(null)

                    setIconCleared(true)

                  }}

                  error={catIconError ?? undefined}

                />

              </FormField>



              <div className="grid grid-cols-1 md:grid-cols-2">

                <FormControl

                  variant="textarea"

                  layout="row"

                  label="Description"

                  placeholder="Description"

                  disabled={fieldsDisabled}

                  error={errors.description?.message}

                  fieldClassName="md:border-r md:border-gray-100"

                  {...register('description')}

                />



                <FormControl

                  layout="row"

                  label="Name"

                  placeholder="Name"

                  disabled={fieldsDisabled}

                  error={errors.name?.message}

                  {...register('name')}

                />

              </div>



              <FormControl

                layout="row"

                label="Question"

                placeholder="Question"

                disabled={fieldsDisabled}

                error={errors.question?.message}

                {...register('question')}

              />



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

