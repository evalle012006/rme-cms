import { FormControl } from '@/components/atoms/FormControl'
import { usePropertySuburbs } from './usePropertySuburbs'

type PropertySuburbSelectProps = {
  postCode: string
  value: string
  onChange: (value: string) => void
  disabled?: boolean
  error?: string
}

export function PropertySuburbSelect({
  postCode,
  value,
  onChange,
  disabled = false,
  error,
}: PropertySuburbSelectProps) {
  const { suburbs, loading } = usePropertySuburbs(postCode)
  const hasPostCode = postCode.trim().length > 0
  const showCurrentValue =
    !!value && !suburbs.some((suburb) => suburb.id === value)

  return (
    <FormControl
      variant="select"
      label="Suburbs"
      disabled={disabled || !hasPostCode || loading}
      error={error}
      value={value}
      onChange={(event) => onChange(event.target.value)}
    >
      <option value="">
        {!hasPostCode
          ? 'Enter post code first'
          : loading
            ? 'Loading suburbs…'
            : 'Select Suburb'}
      </option>
      {showCurrentValue && <option value={value}>{value}</option>}
      {suburbs.map((suburb) => (
        <option key={suburb.id} value={suburb.id}>
          {suburb.text}
        </option>
      ))}
    </FormControl>
  )
}
