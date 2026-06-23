import {
  useState,
  forwardRef,
  type ComponentProps,
  type ReactNode,
} from 'react'
import { Eye, EyeOff } from 'lucide-react'

import { Input } from '@/components/ui/input'
import { Select } from '@/components/ui/select'
import { cn } from '@/lib/utils'

import { fieldId, FormField, type FormFieldLayout } from './FormField'

export type FormControlVariant = 'input' | 'textarea' | 'select' | 'password'

type FormControlShared = {
  label?: string
  error?: string
  required?: boolean
  layout?: FormFieldLayout
  /** Wrapper class when `layout="row"` (entity form rows). */
  fieldClassName?: string
  contentClassName?: string
}

export type FormControlInputProps = FormControlShared &
  Omit<ComponentProps<typeof Input>, 'readOnly'> & {
    variant?: 'input'
    readOnly?: boolean
    alwaysReadOnly?: boolean
  }

export type FormControlTextareaProps = FormControlShared &
  ComponentProps<'textarea'> & {
    variant: 'textarea'
  }

export type FormControlSelectProps = FormControlShared &
  ComponentProps<typeof Select> & {
    variant: 'select'
    children?: ReactNode
  }

export type FormControlPasswordProps = FormControlShared &
  Omit<ComponentProps<typeof Input>, 'type'> & {
    variant: 'password'
  }

export type FormControlProps =
  | FormControlInputProps
  | FormControlTextareaProps
  | FormControlSelectProps
  | FormControlPasswordProps

const textareaClasses =
  'w-full min-h-[120px] resize-y rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-transparent focus:ring-2 focus:ring-primary-400 focus:outline-none disabled:pointer-events-none disabled:opacity-50 aria-invalid:border-red-500 aria-invalid:ring-red-200'

const readOnlyInputClasses =
  'cursor-default bg-gray-100 text-gray-700 focus:ring-0 focus:border-gray-300'

const PasswordControl = forwardRef<
  HTMLInputElement,
  Omit<ComponentProps<typeof Input>, 'type'> & { error?: string }
>(function PasswordControl({ error, className, id, ...props }, ref) {
  const [visible, setVisible] = useState(false)

  return (
    <div className="relative">
      <Input
        ref={ref}
        id={id}
        type={visible ? 'text' : 'password'}
        aria-invalid={!!error}
        className={cn(error && 'border-red-500 focus:ring-red-200', 'pr-10', className)}
        {...props}
      />
      <button
        type="button"
        onClick={() => setVisible((v) => !v)}
        className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
        tabIndex={-1}
        aria-label={visible ? 'Hide password' : 'Show password'}
      >
        {visible ? <EyeOff size={16} /> : <Eye size={16} />}
      </button>
    </div>
  )
})

function splitSharedProps(props: FormControlProps) {
  const {
    label,
    error,
    required,
    layout = 'stacked',
    fieldClassName,
    contentClassName,
    variant = 'input',
    id,
    className,
    ...controlProps
  } = props

  const controlId = fieldId(
    label,
    id ??
      ('name' in controlProps && typeof controlProps.name === 'string'
        ? controlProps.name
        : undefined)
  )

  return {
    label,
    error,
    required,
    layout,
    fieldClassName,
    contentClassName,
    variant,
    controlId,
    className,
    controlProps,
  }
}

export const FormControl = forwardRef<
  HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement,
  FormControlProps
>(function FormControl(props, ref) {
  const {
    label,
    error,
    required,
    layout,
    fieldClassName,
    contentClassName,
    variant,
    controlId,
    className,
    controlProps,
  } = splitSharedProps(props)

  const errorClasses = error && 'border-red-500 focus:ring-red-200'

  let control: ReactNode

  if (variant === 'textarea') {
    control = (
      <textarea
        ref={ref as React.Ref<HTMLTextAreaElement>}
        id={controlId}
        aria-invalid={!!error}
        className={cn(textareaClasses, errorClasses, className)}
        {...(controlProps as ComponentProps<'textarea'>)}
      />
    )
  } else if (variant === 'select') {
    const { children, ...selectProps } = controlProps as FormControlSelectProps
    control = (
      <Select
        ref={ref as React.Ref<HTMLSelectElement>}
        id={controlId}
        aria-invalid={!!error}
        className={cn(errorClasses, className)}
        {...selectProps}
      >
        {children}
      </Select>
    )
  } else if (variant === 'password') {
    control = (
      <PasswordControl
        ref={ref as React.Ref<HTMLInputElement>}
        id={controlId}
        error={error}
        className={className}
        {...(controlProps as Omit<ComponentProps<typeof Input>, 'type'>)}
      />
    )
  } else {
    const { readOnly = false, alwaysReadOnly = false, ...inputProps } =
      controlProps as FormControlInputProps
    const isReadOnly = alwaysReadOnly || readOnly

    control = (
      <Input
        ref={ref as React.Ref<HTMLInputElement>}
        id={controlId}
        readOnly={isReadOnly}
        aria-invalid={!!error}
        className={cn(errorClasses, isReadOnly && readOnlyInputClasses, className)}
        {...inputProps}
      />
    )
  }

  return (
    <FormField
      layout={layout}
      label={label}
      htmlFor={controlId}
      error={error}
      required={required}
      className={fieldClassName}
      contentClassName={contentClassName}
    >
      {control}
    </FormField>
  )
})

FormControl.displayName = 'FormControl'
