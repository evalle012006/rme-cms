function SkeletonField({
  labelWidth,
  inputHeight = 'h-10',
  bordered,
}: {
  labelWidth: string
  inputHeight?: string
  bordered?: boolean
}) {
  return (
    <div
      className={`flex flex-col gap-1.5 px-6 py-5${bordered ? ' md:border-r md:border-gray-100' : ''}`}
    >
      <div className={`h-4 ${labelWidth} animate-pulse rounded bg-gray-200`} />
      <div className={`${inputHeight} animate-pulse rounded-lg bg-gray-200`} />
    </div>
  )
}

export function CategoryEditSkeleton() {
  return (
    <>
      <div className="flex flex-col gap-1.5 px-6 py-5">
        <div className="h-4 w-16 animate-pulse rounded bg-gray-200" />
        <div className="min-h-[120px] w-full animate-pulse rounded-lg bg-gray-200" />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2">
        <SkeletonField labelWidth="w-24" inputHeight="h-24" bordered />
        <SkeletonField labelWidth="w-12" />
      </div>

      <SkeletonField labelWidth="w-20" />

      <div className="grid grid-cols-1 md:grid-cols-2">
        <SkeletonField labelWidth="w-16" bordered />
        <SkeletonField labelWidth="w-28" />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2">
        <SkeletonField labelWidth="w-24" inputHeight="h-24" bordered />
      </div>

      <div className="flex flex-wrap items-center gap-3 px-6 pb-6">
        <div className="h-11 w-24 animate-pulse rounded-lg bg-gray-200" />
        <div className="h-11 w-24 animate-pulse rounded-lg bg-gray-200" />
      </div>
    </>
  )
}
