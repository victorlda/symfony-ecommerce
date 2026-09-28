import type { ComponentProps } from 'react'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

type FormFieldProps = {
  id: string
  label: string
  type?: string
  autoComplete?: string
  inputMode?: ComponentProps<'input'>['inputMode']
  defaultValue?: string
  disabled?: boolean
  required?: boolean
  multiline?: boolean
  hint?: string
  errors?: string[]
}

export function FormField({
  id,
  label,
  type = 'text',
  autoComplete,
  inputMode,
  defaultValue,
  disabled,
  required = true,
  multiline = false,
  hint,
  errors,
}: FormFieldProps) {
  const hintId = `${id}-hint`
  const errorId = `${id}-error`
  const describedBy = [hint && hintId, errors && errorId].filter(Boolean).join(' ') || undefined

  const commonProps = {
    id,
    name: id,
    defaultValue,
    disabled,
    required,
    'aria-invalid': errors ? true : undefined,
    'aria-describedby': describedBy,
  }

  return (
    <div className="space-y-2">
      <Label htmlFor={id}>{label}</Label>

      {multiline ? (
        <Textarea {...commonProps} rows={4} />
      ) : (
        <Input {...commonProps} type={type} autoComplete={autoComplete} inputMode={inputMode} />
      )}

      {hint && (
        <p id={hintId} className="text-sm text-muted-foreground">
          {hint}
        </p>
      )}

      {errors && (
        <div id={errorId} className="space-y-1">
          {errors.map((message) => (
            <p key={message} className="text-sm text-destructive">
              {message}
            </p>
          ))}
        </div>
      )}
    </div>
  )
}
