import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { blogsAPI } from '@/api/blogs/blogsAPIHandler'
import { ROUTES } from '@/constants/routes'
import {
  blogToPreviewData,
  clearStashedBlogPreview,
  formatPreviewDate,
  previewStatusClassName,
  previewStatusLabel,
  readStashedBlogPreview,
  type BlogPreviewData,
} from '@/pages/blogs/blogPreviewShared'
import { cn } from '@/lib/utils'

function AuthorAvatar({ src, alt }: { src: string | null; alt: string }) {
  if (src) {
    return (
      <img
        src={src}
        alt={alt}
        className="h-14 w-14 shrink-0 rounded-full object-cover"
      />
    )
  }

  return (
    <div
      className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gray-200 text-gray-500"
      aria-hidden
    >
      <svg viewBox="0 0 24 24" className="h-8 w-8 fill-current" focusable="false">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v1.2h19.2v-1.2c0-3.2-6.4-4.8-9.6-4.8z" />
      </svg>
    </div>
  )
}

export default function BlogPreviewPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id?: string }>()
  const blogId = idParam ? Number(idParam) : NaN

  const [preview, setPreview] = useState<BlogPreviewData | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let mounted = true

    if (idParam) {
      if (Number.isNaN(blogId)) {
        setError('Invalid blog id')
        setLoading(false)
        return
      }

      setLoading(true)
      blogsAPI
        .getById(blogId, { customErrorMessage: 'Blog not found' })
        .then((blog) => {
          if (!mounted) return
          setPreview(blogToPreviewData(blog))
          setError(null)
        })
        .catch(() => {
          if (!mounted) return
          setError('Blog not found')
          setPreview(null)
        })
        .finally(() => {
          if (mounted) setLoading(false)
        })

      return () => {
        mounted = false
      }
    }

    const stashed = readStashedBlogPreview()
    if (stashed) {
      setPreview(stashed)
      setError(null)
    } else {
      setError('No preview data available. Open Preview from the blog form or list.')
      setPreview(null)
    }
    setLoading(false)

    return () => {
      mounted = false
    }
  }, [idParam, blogId])

  const breadcrumb = [
    { label: 'Home', to: ROUTES.root },
    { label: 'Blogs', to: ROUTES.blogs.list },
    { label: 'Preview' },
  ]

  const backTo = preview?.return_to ?? ROUTES.blogs.list

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Breadcrumb items={breadcrumb} />
        <Button
          type="button"
          color="secondary"
          className="h-auto px-4 py-2"
          onClick={() => {
            if (!idParam) clearStashedBlogPreview()
            navigate(backTo)
          }}
        >
          Close preview
        </Button>
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-10 text-center text-gray-500">
          Loading preview…
        </div>
      ) : error || !preview ? (
        <div className="rounded-lg border border-red-200 bg-red-50 p-10 text-center text-red-700">
          {error ?? 'Preview unavailable'}
        </div>
      ) : (
        <article className="mx-auto max-w-3xl space-y-6 bg-white px-2 py-4 sm:px-4">
          <div className="flex flex-wrap items-center gap-3">
            {preview.category_name ? (
              <div className="flex items-center gap-2 text-sm text-gray-700">
                <span className="inline-block h-3 w-3 shrink-0 bg-teal-500" aria-hidden />
                <span className="text-gray-400">—</span>
                <span>{preview.category_name}</span>
              </div>
            ) : null}
            <span
              className={cn(
                'inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold',
                previewStatusClassName(preview.status)
              )}
            >
              {previewStatusLabel(preview.status)}
            </span>
          </div>

          {preview.title?.trim() ? (
            <h1 className="text-2xl font-semibold text-gray-900 sm:text-3xl">
              {preview.title}
            </h1>
          ) : null}

          {preview.feature_image_url ? (
            <div className="overflow-hidden rounded-md bg-gray-100">
              <img
                src={preview.feature_image_url}
                alt={preview.title || 'Feature image'}
                className="max-h-[420px] w-full object-cover"
              />
            </div>
          ) : (
            <div className="flex h-48 items-center justify-center rounded-md bg-gray-100 text-sm text-gray-500">
              No feature image
            </div>
          )}

          <div className="flex items-center gap-4">
            <AuthorAvatar
              src={preview.author_image_url}
              alt={preview.author_name?.trim() || 'Author'}
            />
            <div className="text-sm text-gray-600">
              <p className="font-semibold text-gray-800">
                Written by {preview.author_name?.trim() || 'anonymous'}
              </p>
              <p className="text-gray-500">
                Posted on {formatPreviewDate(preview.blog_date)}
              </p>
            </div>
          </div>

          {preview.description?.trim() ? (
            <div
              className="prose prose-slate max-w-none text-gray-700 [&_img]:max-w-full"
              dangerouslySetInnerHTML={{ __html: preview.description }}
            />
          ) : (
            <p className="text-sm italic text-gray-500">No description yet.</p>
          )}
        </article>
      )}
    </div>
  )
}
