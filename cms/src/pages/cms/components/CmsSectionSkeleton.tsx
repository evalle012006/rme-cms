import { PageCard } from '@/components/atoms/PageCard'
import { cn } from '@/lib/utils'
import type { CmsSectionType } from '@/pages/cms/shared/cmsPageUtils'

type CmsSectionSkeletonProps = {
  sectionTitle: string
  type: CmsSectionType
}

function SkeletonBar({ className }: { className?: string }) {
  return <div className={cn('animate-pulse rounded bg-gray-200', className)} />
}

function SkeletonField({ labelWidth = 'w-16' }: { labelWidth?: string }) {
  return (
    <div className="flex flex-col gap-1.5">
      <SkeletonBar className={cn('h-4', labelWidth)} />
      <SkeletonBar className="h-10 rounded-lg" />
    </div>
  )
}

function SkeletonBannerImage() {
  return (
    <div className="flex flex-col gap-1.5">
      <SkeletonBar className="h-4 w-28" />
      <SkeletonBar className="min-h-[200px] rounded-lg" />
    </div>
  )
}

function SkeletonDescriptionBox() {
  return (
    <div className="flex flex-col gap-1.5">
      <SkeletonBar className="h-4 w-24" />
      <SkeletonBar className="h-10 rounded-t-lg" />
      <SkeletonBar className="min-h-[180px] rounded-b-lg" />
    </div>
  )
}

function SkeletonBody({ type }: { type: CmsSectionType }) {
  switch (type) {
    case 'banner':
      return (
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <SkeletonBannerImage />
          <SkeletonField labelWidth="w-12" />
        </div>
      )
    case 'reviewRatio':
      return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <SkeletonField labelWidth="w-12" />
          <SkeletonField labelWidth="w-40" />
          <SkeletonField labelWidth="w-32" />
        </div>
      )
    case 'bannerTitleDescription':
      return (
        <div className="space-y-6">
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <SkeletonBannerImage />
            <SkeletonField labelWidth="w-12" />
          </div>
          <SkeletonDescriptionBox />
        </div>
      )
    case 'titleDescription':
      return (
        <div className="space-y-6">
          <SkeletonField labelWidth="w-12" />
          <SkeletonDescriptionBox />
        </div>
      )
    case 'mission':
      return (
        <div className="space-y-6">
          <SkeletonField labelWidth="w-12" />
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <SkeletonDescriptionBox />
            <SkeletonDescriptionBox />
            <SkeletonDescriptionBox />
            <SkeletonDescriptionBox />
          </div>
        </div>
      )
  }
}

export function CmsSectionSkeleton({ sectionTitle, type }: CmsSectionSkeletonProps) {
  return (
    <PageCard
      title={sectionTitle}
      actions={<SkeletonBar className="h-10 min-w-[100px] rounded-lg" />}
      aria-busy
      aria-label={`Loading ${sectionTitle}`}
    >
      <SkeletonBody type={type} />
    </PageCard>
  )
}
