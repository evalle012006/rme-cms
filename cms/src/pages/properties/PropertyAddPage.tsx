import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useForm, Controller } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'

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
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { propertiesAPI } from '@/api/properties/propertiesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { companiesAPI, type Company } from '@/api/companies/companiesAPIHandler'
import {
  categoriesAPI,
  type PropertyCategory,
} from '@/api/property-settings/categoriesAPIHandler'
import { statesAPI, type State } from '@/api/states/statesAPIHandler'
import { ROUTES } from '@/constants/routes'
import {
  DEFAULT_LAT,
  DEFAULT_LNG,
  type PropertyFormData,
  propertySchema,
  resolveReturnPath,
  SectionHeader,
  STATUS_OPTIONS,
  toPropertySubmitPayload,
} from './components/propertyFormShared'
import { PropertySuburbSelect } from './components/PropertySuburbSelect'

// ── Component ────────────────────────────────────────────────────────────────

export default function PropertyAddPage() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()

  const defaultStatus =
    searchParams.get('status') === String(STATUS_OPTIONS[1].value)
      ? STATUS_OPTIONS[1].value
      : STATUS_OPTIONS[0].value

  const returnPath = resolveReturnPath(searchParams.get('from'), defaultStatus)

  // Reference-data dropdowns. Loaded once on mount; failures surface via the
  // global Sonner toast wired into the axios interceptor.
  const [companies, setCompanies] = useState<Company[]>([])
  const [categories, setCategories] = useState<PropertyCategory[]>([])
  const [states, setStates] = useState<State[]>([])

  const [logo, setLogo] = useState<File | null>(null)
  const [logoError, setLogoError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)
  const [searchLocation, setSearchLocation] = useState('')

  const breadcrumb = useMemo(
    () => [
      { label: 'Home', to: ROUTES.root },
      { label: 'Property', to: returnPath },
      { label: 'Add' },
    ],
    [returnPath]
  )

  const {
    register,
    handleSubmit,
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
      status: defaultStatus,
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
      lat: DEFAULT_LAT,
      long: DEFAULT_LNG,
    },
  })

  const postCode = watch('post_code')
  const previousPostCode = useRef<string | null>(null)

  useEffect(() => {
    if (
      previousPostCode.current !== null &&
      previousPostCode.current !== postCode
    ) {
      setValue('suburb', '')
    }
    previousPostCode.current = postCode
  }, [postCode, setValue])

  useEffect(() => {
    companiesAPI
      .getAll()
      .then(setCompanies)
      .catch(() => {})
    categoriesAPI
      .getAll()
      .then(setCategories)
      .catch(() => {})
    statesAPI
      .getAll()
      .then(setStates)
      .catch(() => {})
  }, [])

  const categoryOptions = useMemo<MultiSelectOption[]>(
    () => categories.map((c) => ({ value: c.id, label: c.name })),
    [categories]
  )

  const handleCancel = useCallback(() => {
    navigate(returnPath)
  }, [navigate, returnPath])

  const validateLogo = useCallback(() => {
    if (!logo) {
      setLogoError('Property logo is required')
      return false
    }
    setLogoError(null)
    return true
  }, [logo])

  // ── Google Places callback ─────────────────────────────────────────────────
  // When the user picks a place, populate every location field we were able
  // to resolve. State is mapped from name → id since the field stores the
  // state id (matching the backend's `get-states` shape).
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
        if (match) setValue('state', String(match.id), { shouldValidate: true })
      }
      if (place.lat != null) setValue('lat', String(place.lat))
      if (place.lng != null) setValue('long', String(place.lng))
    },
    [setValue, states]
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
    clearErrors()
    if (!validateLogo() || !logo) return

    setSubmitting(true)
    try {
      await propertiesAPI.create(
        {
          ...toPropertySubmitPayload(data, states),
          logo,
        },
        {
          successMessage: 'Property created successfully',
          customErrorMessage: 'Failed to create property',
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

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />

      <h1 className="text-xl font-semibold text-gray-800">Add Property</h1>

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        {/* ── Section: Property Basic Details ─────────────────────────────── */}
        <Card className="overflow-hidden p-0">
          <SectionHeader title="Property Basic Details" />

          <div className="space-y-5 px-6 py-5">
            <FormField label="Property Logo" error={logoError ?? undefined}>
              <ImageDropzone
                layout="banner"
                value={logo}
                onChange={(file) => {
                  setLogo(file)
                  if (file) setLogoError(null)
                }}
                disabled={submitting}
                error={logoError ?? undefined}
              />
            </FormField>

            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
              <FormControl
                label="Project Name"
                placeholder=""
                disabled={submitting}
                error={errors.project_name?.message}
                {...register('project_name')}
              />

              <FormControl
                variant="select"
                label="Company"
                disabled={submitting}
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
                disabled={submitting}
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
                disabled={submitting}
                error={errors.phone?.message}
                {...register('phone')}
              />

              <FormControl
                label="Website"
                placeholder=""
                disabled={submitting}
                error={errors.website_url?.message}
                {...register('website_url')}
              />

              <FormControl
                variant="textarea"
                label="Description"
                placeholder=""
                disabled={submitting}
                error={errors.description?.message}
                className="min-h-[44px]"
                {...register('description')}
              />
            </div>
          </div>
        </Card>

        {/* ── Section: Property Category ──────────────────────────────────── */}
        <Card className="overflow-hidden p-0">
          <SectionHeader title="Property Category" />

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
                    disabled={submitting}
                    placeholder="Select categories…"
                  />
                </FormField>
              )}
            />
          </div>
        </Card>

        {/* ── Section: Property Location Details ─────────────────────────── */}
        <Card className="overflow-hidden p-0">
          <SectionHeader title="Property Location Details" />

          <div className="space-y-5 px-6 py-5">
            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
              <FormControl
                variant="textarea"
                label="Address"
                placeholder=""
                disabled={submitting}
                error={errors.address?.message}
                className="min-h-[80px]"
                {...register('address')}
              />

              <FormControl
                label="Country"
                placeholder=""
                disabled={submitting}
                error={errors.country?.message}
                {...register('country')}
              />

              <FormControl
                variant="select"
                label="State"
                disabled={submitting}
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
                disabled={submitting}
                error={errors.city?.message}
                {...register('city')}
              />

              <FormControl
                label="Post Code"
                placeholder=""
                disabled={submitting}
                error={errors.post_code?.message}
                {...register('post_code')}
              />

              <Controller
                control={control}
                name="suburb"
                render={({ field }) => (
                  <PropertySuburbSelect
                    postCode={postCode}
                    value={field.value}
                    onChange={field.onChange}
                    disabled={submitting}
                    error={errors.suburb?.message}
                  />
                )}
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
              <LocationAutocomplete
                value={searchLocation}
                onChange={setSearchLocation}
                onPlaceSelected={handlePlaceSelected}
                disabled={submitting}
              />
            </FormField>
          </div>
        </Card>

        {/* ── Footer actions ──────────────────────────────────────────────── */}
        <div className="flex flex-wrap items-center gap-3">
          <Button
            type="submit"
            color="primary"
            disabled={submitting}
            className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? 'Adding…' : 'Add Property'}
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
