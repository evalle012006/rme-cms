import { useCallback, useEffect, useMemo, useState } from 'react'
import { toast } from 'sonner'

import { cmsAPI, normalizeCmsSections, type CmsPageSection } from '@/api/cms/cmsAPIHandler'
import {
  buildCmsSavePayload,
  findCmsSection,
  mapCmsSectionValues,
  type CmsPageConfig,
  type CmsPageSectionConfig,
  type CmsSectionValues,
} from '@/pages/cms/shared/cmsPageUtils'

export function useCmsPage(config: CmsPageConfig) {
  const [sections, setSections] = useState<CmsPageSection[]>([])
  const [loading, setLoading] = useState(true)
  const [loaded, setLoaded] = useState(false)

  const fetchSections = useCallback(async () => {
    setLoading(true)
    try {
      const data = await cmsAPI.getPage(config.page, { showErrorToast: false })
      setSections(normalizeCmsSections(data))
      setLoaded(true)
    } catch {
      toast.error(`Failed to load CMS ${config.page} page data`)
    } finally {
      setLoading(false)
    }
  }, [config.page])

  useEffect(() => {
    fetchSections()
  }, [fetchSections])

  const sectionLookup = useMemo(() => {
    const byKey = new Map<string, CmsPageSection | undefined>()
    config.sections.forEach((sectionConfig) => {
      byKey.set(sectionConfig.key, findCmsSection(sections, sectionConfig))
    })
    return byKey
  }, [config.sections, sections])

  const valuesByKey = useMemo(() => {
    const values = new Map<string, CmsSectionValues>()
    config.sections.forEach((sectionConfig) => {
      values.set(
        sectionConfig.key,
        mapCmsSectionValues(sectionConfig.type, sectionLookup.get(sectionConfig.key))
      )
    })
    return values
  }, [config.sections, sectionLookup])

  const createSaveHandler = useCallback(
    (sectionConfig: CmsPageSectionConfig) => async (values: CmsSectionValues) => {
      const section = sectionLookup.get(sectionConfig.key)
      const payload = buildCmsSavePayload(sectionConfig, section, values)
      await cmsAPI.saveSection(payload, { showErrorToast: false })
      await fetchSections()
    },
    [fetchSections, sectionLookup]
  )

  const saveHandlers = useMemo(() => {
    const handlers = new Map<string, (values: CmsSectionValues) => Promise<void>>()
    config.sections.forEach((sectionConfig) => {
      handlers.set(sectionConfig.key, createSaveHandler(sectionConfig))
    })
    return handlers
  }, [config.sections, createSaveHandler])

  const getValues = useCallback(
    <T extends CmsSectionValues>(key: string) => valuesByKey.get(key) as T,
    [valuesByKey]
  )

  const getSaveHandler = useCallback(
    <T extends CmsSectionValues>(key: string) =>
      saveHandlers.get(key) as (values: T) => Promise<void>,
    [saveHandlers]
  )

  return {
    loading,
    loaded,
    getValues,
    getSaveHandler,
    sections: config.sections,
  }
}
