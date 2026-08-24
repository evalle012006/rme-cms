import { useEffect, useMemo, useState } from 'react'
import { toast } from 'sonner'

import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import {
  categoriesAPI,
  type PropertyCategoryWithAllowedRatings,
} from '@/api/property-settings/categoriesAPIHandler'
import { ratingsAPI, type Rating } from '@/api/ratings/ratingsAPIHandler'
import { CategoryRatingSection } from '@/pages/property-settings/components/CategoryRatingSection'
import type { MultiSelectOption } from '@/components/atoms/MultiSelect'
import { Card } from '@/components/ui/card'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Project Category', to: ROUTES.propertySettings.categories.list },
  { label: 'Rating' },
]

function decodeHtml(text: string): string {
  const el = document.createElement('textarea')
  el.innerHTML = text
  return el.value
}

function buildSelections(
  categoryList: PropertyCategoryWithAllowedRatings[]
): Record<number, number[]> {
  const nextSelections: Record<number, number[]> = {}

  categoryList.forEach((category) => {
    nextSelections[category.id] = (category.property_allowed_rating ?? []).map(
      (rating) => rating.id
    )
  })

  return nextSelections
}

export default function ManageRatingPage() {
  const [categories, setCategories] = useState<PropertyCategoryWithAllowedRatings[]>(
    []
  )
  const [ratingOptions, setRatingOptions] = useState<MultiSelectOption[]>([])
  const [selections, setSelections] = useState<Record<number, number[]>>({})
  const [loading, setLoading] = useState(true)
  const [savingCategoryId, setSavingCategoryId] = useState<number | null>(null)

  useEffect(() => {
    let mounted = true

    const loadPage = async () => {
      setLoading(true)
      try {
        const [categoryList, ratings] = await Promise.all([
          categoriesAPI.getPropertiesWithAllowedRatings(),
          ratingsAPI.getConfiguration(),
        ])

        if (!mounted) return

        const options: MultiSelectOption[] = (ratings ?? []).map(
          (rating: Rating) => ({
            value: rating.id,
            label: decodeHtml(rating.name),
          })
        )

        setCategories(categoryList)
        setRatingOptions(options)
        setSelections(buildSelections(categoryList))
      } catch {
        if (mounted) {
          toast.error('Failed to load manage rating data')
        }
      } finally {
        if (mounted) setLoading(false)
      }
    }

    loadPage()

    return () => {
      mounted = false
    }
  }, [])

  const handleSelectionChange = (categoryId: number, ratingIds: number[]) => {
    setSelections((prev) => ({ ...prev, [categoryId]: ratingIds }))
  }

  const handleSave = async (categoryId: number) => {
    const ratingIds = selections[categoryId] ?? []

    setSavingCategoryId(categoryId)
    try {
      const result = await categoriesAPI.setPropertyAllowedRatings({
        property_id: categoryId,
        rating_ids: ratingIds,
      })

      if (result === 'success') {
        toast.success('Category ratings updated successfully')
      } else {
        toast.error('Failed to update category ratings')
      }
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setSavingCategoryId(null)
    }
  }

  const sections = useMemo(
    () =>
      categories.map((category) => ({
        id: category.id,
        name: decodeHtml(category.name),
        value: selections[category.id] ?? [],
      })),
    [categories, selections]
  )

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      {loading ? (
        <div className="space-y-6">
          {Array.from({ length: 3 }).map((_, i) => (
            <Card key={i} className="overflow-visible p-0 shadow-none">
              <div className="border-b border-gray-100 px-6 py-4">
                <div className="h-5 w-40 animate-pulse rounded bg-gray-200" />
              </div>

              <div className="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start">
                <div className="h-10 min-w-0 flex-1 animate-pulse rounded-lg bg-gray-200" />
                <div className="h-10 w-24 shrink-0 animate-pulse rounded-lg bg-gray-200 sm:mt-0.5" />
              </div>
            </Card>
          ))}
        </div>
      ) : (
        <div className="space-y-6">
          {sections.map((section) => (
            <CategoryRatingSection
              key={section.id}
              categoryName={section.name}
              options={ratingOptions}
              value={section.value}
              onChange={(ratingIds) => handleSelectionChange(section.id, ratingIds)}
              onSave={() => handleSave(section.id)}
              saving={savingCategoryId === section.id}
            />
          ))}
        </div>
      )}
    </div>
  )
}
