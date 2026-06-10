import { useCallback, useMemo, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { FormField } from '@/components/atoms/FormField'
import { ImageDropzone } from '@/components/atoms/ImageDropzone'
import { FormControl } from '@/components/atoms/FormControl'
import { Card } from '@/components/ui/card'
import {
  companiesAPI,
  COMPANY_STATUS,
} from '@/api/companies/companiesAPIHandler'
import { isValidationError } from '@/api/apiService'
import { ROUTES } from '@/constants/routes'

const companySchema = z.object({
  company_name: z.string().min(1, 'Company name is required'),
  contact_number: z.string().min(1, 'Contact number is required'),
  website: z.string().min(1, 'Website is required'),
  status: z.string().min(1, 'Status is required'),
  bio: z.string().min(1, 'Company bio is required'),
})

type CompanyFormData = z.infer<typeof companySchema>

const STATUS_OPTIONS = [
  { value: String(COMPANY_STATUS.PENDING), label: 'Pending' },
  { value: String(COMPANY_STATUS.APPROVED), label: 'Approved' },
] as const

function resolveReturnPath(status: string) {
  return status === String(COMPANY_STATUS.APPROVED)
    ? ROUTES.company.approved
    : ROUTES.company.pending
}

export default function CompanyAddPage() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()

  const defaultStatus =
    searchParams.get('status') === String(COMPANY_STATUS.APPROVED)
      ? String(COMPANY_STATUS.APPROVED)
      : String(COMPANY_STATUS.PENDING)

  const returnPath = resolveReturnPath(defaultStatus)
  const isFromApproved = defaultStatus === String(COMPANY_STATUS.APPROVED)

  const [logo, setLogo] = useState<File | null>(null)
  const [logoError, setLogoError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const breadcrumb = useMemo(
    () => [
      { label: 'Home', to: ROUTES.root },
      {
        label: isFromApproved ? 'Company Approved' : 'Company Pending',
        to: isFromApproved ? ROUTES.company.approved : ROUTES.company.pending,
      },
      { label: 'Add Company' },
    ],
    [isFromApproved]
  )

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      company_name: '',
      contact_number: '',
      website: '',
      status: defaultStatus,
      bio: '',
    },
  })

  const handleCancel = useCallback(() => {
    navigate(returnPath)
  }, [navigate, returnPath])

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof CompanyFormData)[] = [
      'company_name',
      'contact_number',
      'website',
      'status',
      'bio',
    ]

    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: 'server', message })
      }
    })

    const logoMessage = apiErrors.logo?.[0]
    if (logoMessage) {
      setLogoError(logoMessage)
    }
  }

  const validateLogo = useCallback(() => {
    if (!logo) {
      setLogoError('Company logo is required')
      return false
    }
    setLogoError(null)
    return true
  }, [logo])

  const onSubmit = async (data: CompanyFormData) => {
    clearErrors()
    if (!validateLogo() || !logo) return

    setSubmitting(true)
    try {
      await companiesAPI.create(
        {
          company_name: data.company_name,
          bio: data.bio,
          status: Number(data.status),
          contact_number: data.contact_number,
          website: data.website,
          logo,
        },
        {
          successMessage: 'Company created successfully',
          customErrorMessage: 'Failed to create company',
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

      <form
        onSubmit={handleSubmit(onSubmit, onInvalid)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800">
              Add Company Detail
            </h2>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)]">
            <FormField
              layout="row"
              label="Company Logo"
              error={logoError ?? undefined}
              className="border-b border-gray-100 lg:border-r lg:border-b-0"
              contentClassName="flex justify-center lg:justify-start"
            >
              <ImageDropzone
                value={logo}
                onChange={(file) => {
                  setLogo(file)
                  if (file) setLogoError(null)
                }}
                disabled={submitting}
                layout="square"
                className="w-full max-w-[220px]"
                error={logoError ?? undefined}
              />
            </FormField>

            <div className="grid grid-cols-1 md:grid-cols-2">
              <FormControl
                layout="row"
                label="Company Name"
                placeholder="Company name"
                disabled={submitting}
                error={errors.company_name?.message}
                fieldClassName="md:border-r md:border-gray-100"
                {...register('company_name')}
              />

              <FormControl
                layout="row"
                label="Contact Number"
                placeholder="Contact number"
                disabled={submitting}
                error={errors.contact_number?.message}
                {...register('contact_number')}
              />

              <FormControl
                layout="row"
                label="Website"
                placeholder="Website"
                disabled={submitting}
                error={errors.website?.message}
                fieldClassName="md:border-r md:border-gray-100"
                {...register('website')}
              />

              <FormControl
                variant="select"
                layout="row"
                label="Status"
                disabled={submitting}
                error={errors.status?.message}
                {...register('status')}
              >
                <option value="" disabled>
                  Select status
                </option>
                {STATUS_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </FormControl>
            </div>
          </div>

          <FormControl
            variant="textarea"
            layout="row"
            label="Company Bio"
            placeholder="Company bio"
            disabled={submitting}
            error={errors.bio?.message}
            {...register('bio')}
          />

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
