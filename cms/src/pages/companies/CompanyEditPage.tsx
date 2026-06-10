import { useCallback, useEffect, useMemo, useState } from 'react'
import { useNavigate, useParams, useSearchParams } from 'react-router-dom'
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
  companiesAPI,
  COMPANY_STATUS,
  type CompanyDetail,
  type CompanyUser,
} from '@/api/companies/companiesAPIHandler'
import { isValidationError } from '@/api/apiService'
import {
  CompanyUsersSection,
  type CompanyUserStatus,
} from './components/CompanyUsersSection'
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

function resolveReturnPath(from: string | null) {
  return from === 'pending' ? ROUTES.company.pending : ROUTES.company.approved
}

function toFormValues(company: CompanyDetail): CompanyFormData {
  return {
    company_name: company.company_name,
    contact_number: company.contact_number ?? '',
    website: company.website ?? '',
    status: String(company.status),
    bio: company.bio ?? '',
  }
}

function toUserStatuses(users: CompanyUser[]): CompanyUserStatus[] {
  return users.map((user) => ({
    id: user.id,
    status: !!user.email_verified_at,
  }))
}

export default function CompanyEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const [searchParams] = useSearchParams()
  const companyId = Number(idParam)
  const returnPath = resolveReturnPath(searchParams.get('from'))
  const isFromPending = searchParams.get('from') === 'pending'

  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [original, setOriginal] = useState<CompanyDetail | null>(null)

  const [logo, setLogo] = useState<File | null>(null)
  const [existingLogoUrl, setExistingLogoUrl] = useState<string | null>(null)
  const [logoCleared, setLogoCleared] = useState(false)
  const [logoError, setLogoError] = useState<string | null>(null)

  const [companyUsers, setCompanyUsers] = useState<CompanyUser[]>([])
  const [userStatuses, setUserStatuses] = useState<CompanyUserStatus[]>([])

  const breadcrumb = useMemo(
    () => [
      { label: 'Home', to: ROUTES.root },
      {
        label: isFromPending ? 'Company Pending' : 'Company Approved',
        to: isFromPending ? ROUTES.company.pending : ROUTES.company.approved,
      },
      { label: 'Edit Company' },
    ],
    [isFromPending]
  )

  const {
    register,
    handleSubmit,
    reset,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      company_name: '',
      contact_number: '',
      website: '',
      status: String(COMPANY_STATUS.APPROVED),
      bio: '',
    },
  })

  const applyCompany = useCallback(
    (company: CompanyDetail) => {
      setOriginal(company)
      reset(toFormValues(company))
      setExistingLogoUrl(company.full_company_logo)
      setLogo(null)
      setLogoCleared(false)
      setLogoError(null)
      setCompanyUsers(company.company_users ?? [])
      setUserStatuses(toUserStatuses(company.company_users ?? []))
    },
    [reset]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(companyId)) {
      toast.error('Invalid company id')
      navigate(returnPath)
      return
    }

    let mounted = true
    setLoading(true)

    companiesAPI
      .getById(companyId)
      .then((res) => {
        if (!mounted) return
        if (!res.success || !res.data) {
          toast.error('Company not found')
          navigate(returnPath)
          return
        }
        applyCompany(res.data)
      })
      .catch(() => {
        if (mounted) navigate(returnPath)
      })
      .finally(() => mounted && setLoading(false))

    return () => {
      mounted = false
    }
  }, [idParam, companyId, navigate, returnPath, applyCompany])

  const hasLogo = !!logo || (!!existingLogoUrl && !logoCleared)

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
    if (!hasLogo) {
      setLogoError('Company logo is required')
      return false
    }
    setLogoError(null)
    return true
  }, [hasLogo])

  const handleUserStatusChange = useCallback((userId: number, status: boolean) => {
    setUserStatuses((prev) =>
      prev.map((entry) =>
        entry.id === userId ? { ...entry, status } : entry
      )
    )
  }, [])

  const onSubmit = async (data: CompanyFormData) => {
    if (!original) return
    clearErrors()
    if (!validateLogo()) return

    const logoPayload = logo ?? (logoCleared ? null : original.logo)
    if (!logoPayload) return

    setSubmitting(true)
    try {
      await companiesAPI.update(
        {
          id: original.id,
          company_name: data.company_name,
          bio: data.bio,
          status: Number(data.status),
          contact_number: data.contact_number,
          website: data.website,
          logo: logoPayload,
          users: userStatuses,
        },
        {
          successMessage: 'Company updated successfully',
          customErrorMessage: 'Failed to update company',
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
          <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800">
              Edit Company Detail
            </h2>
          </div>

          {loading ? (
            <div className="px-6 py-12 text-center text-sm text-gray-500">
              Loading…
            </div>
          ) : (
            <>
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
                    existingImageUrl={logoCleared ? null : existingLogoUrl}
                    disabled={fieldsDisabled}
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
                    disabled={fieldsDisabled}
                    error={errors.company_name?.message}
                    fieldClassName="md:border-r md:border-gray-100"
                    {...register('company_name')}
                  />

                  <FormControl
                    layout="row"
                    label="Contact Number"
                    placeholder="Contact number"
                    disabled={fieldsDisabled}
                    error={errors.contact_number?.message}
                    {...register('contact_number')}
                  />

                  <FormControl
                    layout="row"
                    label="Website"
                    placeholder="Website"
                    disabled={fieldsDisabled}
                    error={errors.website?.message}
                    fieldClassName="md:border-r md:border-gray-100"
                    {...register('website')}
                  />

                  <FormControl
                    variant="select"
                    layout="row"
                    label="Status"
                    disabled={fieldsDisabled}
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
                disabled={fieldsDisabled}
                error={errors.bio?.message}
                {...register('bio')}
              />

              <CompanyUsersSection
                users={companyUsers}
                userStatuses={userStatuses}
                disabled={fieldsDisabled}
                onStatusChange={handleUserStatusChange}
              />

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
                  onClick={() => navigate(returnPath)}
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
