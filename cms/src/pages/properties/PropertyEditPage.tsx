import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useNavigate, useParams, useSearchParams } from 'react-router-dom'
import { useForm, Controller } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { toast } from 'sonner'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { FormField } from '@/components/atoms/FormField'
import { FormControl } from '@/components/atoms/FormControl'
import { ImageDropzone } from '@/components/atoms/ImageDropzone'
import { MultiSelect, type MultiSelectOption } from '@/components/atoms/MultiSelect'
import {
  LocationAutocomplete,
  type ResolvedPlace,
} from '@/components/atoms/LocationAutocomplete'
import { LocationMap } from '@/components/atoms/LocationMap'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  propertiesAPI,
  type Property,
} from '@/api/properties/propertiesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { companiesAPI, type Company } from '@/api/companies/companiesAPIHandler'
import {
  categoriesAPI,
  type PropertyCategory,
} from '@/api/property-settings/categoriesAPIHandler'
import { statesAPI, type State } from '@/api/states/statesAPIHandler'
import { ROUTES } from '@/constants/routes'
import {
  type PropertyFormData,
  propertySchema,
  resolveReturnPath,
  SectionHeader,
  STATUS_OPTIONS,
  toFormValues,
  toPropertySubmitPayload,
} from './components/propertyFormShared'
import { PropertySuburbSelect } from './components/PropertySuburbSelect'

export default function PropertyEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const [searchParams] = useSearchParams()
  const propertyId = Number(idParam)
  const returnPath = resolveReturnPath(searchParams.get('from'))

  const [original, setOriginal] = useState<Property | null>(null)
  const [loading, setLoading] = useState(true)
  const [companies, setCompanies] = useState<Company[]>([])
  const [categories, setCategories] = useState<PropertyCategory[]>([])
  const [states, setStates] = useState<State[]>([])

  const [logo, setLogo] = useState<File | null>(null)
  const [existingLogoUrl, setExistingLogoUrl] = useState<string | null>(null)
  const [logoCleared, setLogoCleared] = useState(false)
  const [logoError, setLogoError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)
  const [searchLocation, setSearchLocation] = useState('')

  const breadcrumb = useMemo(
    () => [
      { label: 'Home', to: ROUTES.root },
      { label: 'Project', to: returnPath },
      { label: 'Edit' },
    ],
    [returnPath]
  )

  const {
    register,
    handleSubmit,
    reset,
    setError,
    clearErrors,
    setValue,
    control,
    watch,
    formState: { errors },
  } = useForm<PropertyFormData>({
    resolver: zodResolver(propertySchema),
    defaultValues: {
      project_name: '',
      developer_name: '',
      status: '',
      phone: '',
      website_url: '',
      description: '',
      property_categories: [],
      address: '',
      country: '',
      state: '',
      city: '',
      post_code: '',
      suburb: '',
      lat: '',
      long: '',
    },
  })

  const stateId = watch('state')
  const postCode = watch('post_code')
  const lat = watch('lat')
  const long = watch('long')
  const previousState = useRef<string | null>(null)

  useEffect(() => {
    if (
      previousState.current !== null &&
      previousState.current !== stateId
    ) {
      setValue('suburb', '')
      setValue('post_code', '')
    }
    previousState.current = stateId
  }, [stateId, setValue])

  const handleSuburbChange = useCallback(
    (suburbName: string, nextPostCode: string) => {
      setValue('suburb', suburbName, { shouldValidate: true })
      setValue('post_code', nextPostCode, { shouldValidate: true })
    },
    [setValue]
  )

  const applyProperty = useCallback(
    (property: Property, loadedStates: State[]) => {
      const values = toFormValues(property, loadedStates)
      setOriginal(property)
      previousState.current = values.state
      reset(values)
      setExistingLogoUrl(property.full_property_logo)
      setLogo(null)
      setLogoCleared(false)
      setLogoError(null)
    },
    [reset]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(propertyId)) {
      toast.error('Invalid project id')
      navigate(returnPath)
      return
    }

    let mounted = true
    setLoading(true)

    Promise.all([
      companiesAPI.getAll(),
      categoriesAPI.getAll(),
      statesAPI.getAll(),
      propertiesAPI.getById(propertyId, {
        customErrorMessage: 'Project not found',
      }),
    ])
      .then(([companyList, categoryList, loadedStates, property]) => {
        if (!mounted) return
        setCompanies(companyList)
        setCategories(categoryList)
        setStates(loadedStates)
        applyProperty(property, loadedStates)
      })
      .catch(() => {
        if (mounted) navigate(returnPath)
      })
      .finally(() => {
        if (mounted) setLoading(false)
      })

    return () => {
      mounted = false
    }
  }, [idParam, propertyId, navigate, returnPath, applyProperty])

  const categoryOptions = useMemo<MultiSelectOption[]>(
    () => categories.map((c) => ({ value: c.id, label: c.name })),
    [categories]
  )

  const fieldsDisabled = loading || submitting
  const hasLogo = !!logo || (!!existingLogoUrl && !logoCleared)

  const handleCancel = useCallback(() => {
    navigate(returnPath)
  }, [navigate, returnPath])

  const validateLogo = useCallback(() => {
    if (!hasLogo) {
      setLogoError('Project logo is required')
      return false
    }
    setLogoError(null)
    return true
  }, [hasLogo])

  const handlePlaceSelected = useCallback(
    (place: ResolvedPlace) => {
      if (place.address) setValue('address', place.address, { shouldValidate: true })
      if (place.country) setValue('country', place.country, { shouldValidate: true })
      if (place.city) setValue('city', place.city, { shouldValidate: true })
      if (place.post_code) setValue('post_code', place.post_code, { shouldValidate: true })
      if (place.suburb) setValue('suburb', place.suburb, { shouldValidate: true })

      if (place.state) {
        const match = states.find(
          (s) => s.state_name.toLowerCase() === place.state.toLowerCase()
        )
        if (match) {
          previousState.current = String(match.id)
          setValue('state', String(match.id), { shouldValidate: true })
        }
      }
      if (place.lat != null) setValue('lat', String(place.lat))
      if (place.lng != null) setValue('long', String(place.lng))
    },
    [setValue, states]
  )

  const handleMapLocationChange = useCallback(
    (nextLat: number, nextLng: number) => {
      setValue('lat', String(nextLat))
      setValue('long', String(nextLng))
    },
    [setValue]
  )

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof PropertyFormData)[] = [
      'project_name',
      'developer_name',
      'status',
      'phone',
      'website_url',
      'description',
      'property_categories',
      'address',
      'country',
      'state',
      'city',
      'post_code',
      'suburb',
      'lat',
      'long',
    ]

    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) setError(key, { type: 'server', message })
    })

    const logoMessage = apiErrors.logo?.[0]
    if (logoMessage) setLogoError(logoMessage)

    const categoryMessage =
      apiErrors.property_categories?.[0] ?? apiErrors.property_category?.[0]
    if (categoryMessage) {
      setError('property_categories', { type: 'server', message: categoryMessage })
    }
  }

  const onSubmit = async (data: PropertyFormData) => {
    if (!original) return
    clearErrors()
    if (!validateLogo()) return

    const logoPayload = logo ?? (logoCleared ? null : original.logo)
    if (!logoPayload) return

    setSubmitting(true)
    try {
      await propertiesAPI.update(
        {
          id: original.id,
          ...toPropertySubmitPayload(data, states),
          logo: logoPayload,
        },
        {
          successMessage: 'Project updated successfully',
          customErrorMessage: 'Failed to update project',
        }
      )
      navigate(returnPath)
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
    validateLogo()
  }

  if (loading) {
    return (
      <div className="space-y-6">
        <Breadcrumb items={breadcrumb} />

        <div className="h-7 w-40 animate-pulse rounded bg-gray-200" />

        <div className="space-y-6">
          <Card className="overflow-hidden p-0">
            <div className="border-b border-gray-200 bg-gray-50 px-6 py-3">
              <div className="h-4 w-44 animate-pulse rounded bg-gray-200" />
            </div>
            <div className="space-y-5 px-6 py-5">
              <div className="h-32 w-full animate-pulse rounded-lg bg-gray-200" />
              <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                {Array.from({ length: 6 }).map((_, i) => (
                  <div key={i} className="space-y-2">
                    <div className="h-4 w-24 animate-pulse rounded bg-gray-200" />
                    <div className="h-10 animate-pulse rounded-lg bg-gray-200" />
                  </div>
                ))}
              </div>
            </div>
          </Card>

          <Card className="overflow-hidden p-0">
            <div className="border-b border-gray-200 bg-gray-50 px-6 py-3">
              <div className="h-4 w-36 animate-pulse rounded bg-gray-200" />
            </div>
            <div className="px-6 py-5">
              <div className="h-10 animate-pulse rounded-lg bg-gray-200" />
            </div>
          </Card>

          <Card className="overflow-hidden p-0">
            <div className="border-b border-gray-200 bg-gray-50 px-6 py-3">
              <div className="h-4 w-48 animate-pulse rounded bg-gray-200" />
            </div>
            <div className="space-y-5 px-6 py-5">
              <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                {Array.from({ length: 8 }).map((_, i) => (
                  <div key={i} className="space-y-2">
                    <div className="h-4 w-20 animate-pulse rounded bg-gray-200" />
                    <div className="h-10 animate-pulse rounded-lg bg-gray-200" />
                  </div>
                ))}
              </div>
              <div className="space-y-2">
                <div className="h-4 w-32 animate-pulse rounded bg-gray-200" />
                <div className="h-10 animate-pulse rounded-lg bg-gray-200" />
              </div>
            </div>
          </Card>

          <div className="flex flex-wrap items-center gap-3">
            <div className="h-11 w-36 animate-pulse rounded-lg bg-gray-200" />
            <div className="h-11 w-24 animate-pulse rounded-lg bg-gray-200" />
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />

      <h1 className="text-xl font-semibold text-gray-800">Edit Project</h1>

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          <SectionHeader title="Project Basic Details" />

          <div className="space-y-5 px-6 py-5">
            <FormField label="Project Logo" error={logoError ?? undefined}>
              <ImageDropzone
                layout="banner"
                value={logo}
                existingImageUrl={logoCleared ? null : existingLogoUrl}
                onChange={(file) => {
                  setLogo(file)
                  if (file) {
                    setLogoError(null)
                    setLogoCleared(false)
                  }
                }}
                onClearExisting={() => {
                  setExistingLogoUrl(null)
                  setLogoCleared(true)
                }}
                disabled={fieldsDisabled}
                error={logoError ?? undefined}
              />
            </FormField>

            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
              <FormControl
                label="Project Name"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.project_name?.message}
                {...register('project_name')}
              />

              <FormControl
                variant="select"
                label="Company"
                disabled={fieldsDisabled}
                error={errors.developer_name?.message}
                {...register('developer_name')}
              >
                <option value="">Select Company</option>
                {companies.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.company_name}
                  </option>
                ))}
              </FormControl>

              <FormControl
                variant="select"
                label="Status"
                disabled={fieldsDisabled}
                error={errors.status?.message}
                {...register('status')}
              >
                <option value="">Select Status</option>
                {STATUS_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </FormControl>

              <FormControl
                label="Phone"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.phone?.message}
                {...register('phone')}
              />

              <FormControl
                label="Website"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.website_url?.message}
                {...register('website_url')}
              />

              <FormControl
                variant="textarea"
                label="Description"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.description?.message}
                className="min-h-[44px]"
                {...register('description')}
              />
            </div>
          </div>
        </Card>

        <Card className="overflow-hidden p-0">
          <SectionHeader title="Project Category" />

          <div className="px-6 py-5">
            <Controller
              control={control}
              name="property_categories"
              render={({ field }) => (
                <FormField error={errors.property_categories?.message}>
                  <MultiSelect
                    options={categoryOptions}
                    value={field.value}
                    onChange={field.onChange}
                    disabled={fieldsDisabled}
                    placeholder="Select categories…"
                  />
                </FormField>
              )}
            />
          </div>
        </Card>

        <Card className="overflow-hidden p-0">
          <SectionHeader title="Project Location Details" />

          <div className="space-y-5 px-6 py-5">
            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
              <FormControl
                variant="textarea"
                label="Address"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.address?.message}
                className="min-h-[80px]"
                {...register('address')}
              />

              <FormControl
                label="Country"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.country?.message}
                {...register('country')}
              />

              <FormControl
                variant="select"
                label="State"
                disabled={fieldsDisabled}
                error={errors.state?.message}
                {...register('state')}
              >
                <option value="">Select State</option>
                {states.map((s) => (
                  <option key={s.id} value={s.id}>
                    {s.state_name}
                  </option>
                ))}
              </FormControl>

              <FormControl
                label="City"
                placeholder=""
                disabled={fieldsDisabled}
                error={errors.city?.message}
                {...register('city')}
              />

              <Controller
                control={control}
                name="suburb"
                render={({ field }) => (
                  <PropertySuburbSelect
                    stateId={stateId}
                    postCode={postCode}
                    value={field.value}
                    onChange={handleSuburbChange}
                    disabled={fieldsDisabled}
                    error={errors.suburb?.message}
                  />
                )}
              />

              <FormControl
                label="Post Code"
                placeholder=""
                alwaysReadOnly
                error={errors.post_code?.message}
                {...register('post_code')}
              />

              <FormField label="Latitude">
                <Input
                  type="text"
                  readOnly
                  className="cursor-default bg-gray-100 text-gray-700 focus:border-gray-300 focus:ring-0"
                  {...register('lat')}
                />
              </FormField>

              <FormField label="Longitude">
                <Input
                  type="text"
                  readOnly
                  className="cursor-default bg-gray-100 text-gray-700 focus:border-gray-300 focus:ring-0"
                  {...register('long')}
                />
              </FormField>
            </div>

            <FormField label="Search Location">
              <div className="space-y-3">
                <LocationAutocomplete
                  value={searchLocation}
                  onChange={setSearchLocation}
                  onPlaceSelected={handlePlaceSelected}
                  disabled={fieldsDisabled}
                />
                <LocationMap
                  lat={lat}
                  lng={long}
                  onLocationChange={handleMapLocationChange}
                  disabled={fieldsDisabled}
                />
              </div>
            </FormField>
          </div>
        </Card>

        <div className="flex flex-wrap items-center gap-3">
          <Button
            type="submit"
            color="primary"
            disabled={submitting}
            className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? 'Updating…' : 'Update Project'}
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
      </form>
    </div>
  )
}
