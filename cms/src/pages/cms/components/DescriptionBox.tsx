import { useMemo } from 'react'
import ReactQuill from 'react-quill-new'
import { cn } from '@/lib/utils'
import 'react-quill-new/dist/quill.snow.css'

export type DescriptionBoxProps = {
  label?: string
  value?: string
  onChange?: (html: string) => void
  disabled?: boolean
  className?: string
}

const formats = [
  'header',
  'bold',
  'italic',
  'link',
  'list',
  'indent',
  'blockquote',
  'image',
  'video',
]

export function DescriptionBox({
  label = 'Description',
  value = '',
  onChange,
  disabled = false,
  className,
}: DescriptionBoxProps) {
  const modules = useMemo(
    () => ({
      toolbar: {
        container: [
          [{ header: [2, 3, 4, false] }],
          ['bold', 'italic', 'link'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ indent: '-1' }, { indent: '+1' }],
          ['image', 'blockquote', 'video'],
          ['undo', 'redo'],
        ],
        handlers: {
          undo(this: { quill: { history: { undo: () => void } } }) {
            this.quill.history.undo()
          },
          redo(this: { quill: { history: { redo: () => void } } }) {
            this.quill.history.redo()
          },
        },
      },
      history: {
        delay: 500,
        maxStack: 100,
        userOnly: true,
      },
    }),
    []
  )

  return (
    <div className={className}>
      <p className="mb-1.5 text-sm font-medium text-gray-700">{label}</p>
      <div
        className={cn(
          'description-box overflow-visible rounded-md',
          '[&_.ql-toolbar]:rounded-t-md [&_.ql-toolbar]:border-0 [&_.ql-toolbar]:border-b [&_.ql-toolbar]:border-gray-300 [&_.ql-toolbar]:bg-gray-50',
          '[&_.ql-container]:overflow-visible [&_.ql-container]:border-0 [&_.ql-editor]:min-h-[180px] [&_.ql-editor]:text-sm [&_.ql-editor]:text-gray-900',
          disabled && '[&_.ql-toolbar]:hidden [&_.ql-container]:bg-gray-100 [&_.ql-editor]:text-gray-700'
        )}
      >
        <ReactQuill
          theme="snow"
          value={value}
          onChange={(html) => onChange?.(html)}
          readOnly={disabled}
          modules={modules}
          formats={formats}
          placeholder={disabled ? undefined : 'Enter description…'}
        />
      </div>
    </div>
  )
}
