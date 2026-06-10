import { useEffect, useState } from 'react'
import {
  suburbsAPI,
  type SuburbOption,
} from '@/api/suburbs/suburbsAPIHandler'

export function usePropertySuburbs(postCode: string) {
  const [suburbs, setSuburbs] = useState<SuburbOption[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const trimmed = postCode.trim()
    if (!trimmed) {
      setSuburbs([])
      return
    }

    let cancelled = false
    setLoading(true)

    suburbsAPI
      .fetch({ postcode: trimmed, q: '' })
      .then((res) => {
        if (!cancelled) setSuburbs(res.results ?? [])
      })
      .catch(() => {
        if (!cancelled) setSuburbs([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [postCode])

  return { suburbs, loading }
}
