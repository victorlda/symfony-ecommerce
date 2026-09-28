import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

type FormFieldProps = {
  id: string
  label: string
  type?: string
  autoComplete?: string
  errors?: string[]
}

export function FormField({ id, label, type = 'text', autoComplete, errors }: FormFieldProps) {
  const errorId = `${id}-error`

  return (
    <div className="space-y-2">
      <Label htmlFor={id}>{label}</Label>
      <Input
        id={id}
        name={id}
        type={type}
        autoComplete={autoComplete}
        required
        aria-invalid={errors ? true : undefined}
        aria-describedby={errors ? errorId : undefined}
      />
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
