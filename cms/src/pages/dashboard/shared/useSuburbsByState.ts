import { useEffect, useState } from 'react'
import { suburbsAPI } from '@/api/suburbs/suburbsAPIHandler'
import type { SelectDropdownOption } from '@/components/atoms/SelectDropdown'

export function useSuburbsByState(stateId: string) {
  const [suburbOptions, setSuburbOptions] = useState<SelectDropdownOption[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (!stateId) {
      setSuburbOptions([])
      setLoading(false)
      return
    }

    let cancelled = false
    // Clear previous state's suburbs immediately while the new list loads.
    setSuburbOptions([])
    setLoading(true)

    suburbsAPI
      .byState({ state_id: stateId }, { showErrorToast: false })
      .then((rows) => {
        if (cancelled) return
        setSuburbOptions(
          (rows ?? []).map((row) => ({
            value: String(row.id ?? row.name),
            label: row.name,
          }))
        )
      })
      .catch(() => {
        if (!cancelled) setSuburbOptions([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [stateId])

  return { suburbOptions, loading }
}
