import { useState, type FormEvent } from 'react'
import type { components } from '@/api/schema'
import { ApiError } from '@/api/problem'
import { FormField } from '@/components/FormField'
import { Button } from '@/components/ui/button'
import { centsToInput, parsePriceInput } from '@/lib/format'

export type Product = components['schemas']['ProductResponse']

export type ProductFormValues = {
  sku: string
  name: string
  priceInCents: number
  description: string | null
}

type ProductFormProps = {
  product?: Product
  submitLabel: string
  onSubmit: (values: ProductFormValues) => Promise<unknown>
}

export function ProductForm({ product, submitLabel, onSubmit }: ProductFormProps) {
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)
  const editing = product !== undefined

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    const form = new FormData(event.currentTarget)
    const description = String(form.get('description') ?? '').trim()

    setSubmitting(true)
    setFieldErrors({})
    setError(null)

    try {
      await onSubmit({
        // Campos desabilitados não entram no FormData; na edição, o SKU vem do produto.
        sku: String(form.get('sku') ?? product?.sku ?? ''),
        name: String(form.get('name')),
        priceInCents: parsePriceInput(String(form.get('priceInCents'))),
        description: description === '' ? null : description,
      })
    } catch (e) {
      if (e instanceof ApiError && e.problem.code === 'validation_failed') {
        setFieldErrors(e.fieldErrors())
      } else {
        setError(e instanceof ApiError ? e.problem.detail : 'Não foi possível salvar o produto.')
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <FormField
        id="sku"
        label="SKU"
        defaultValue={product?.sku}
        disabled={editing}
        hint={editing ? 'O SKU não pode ser alterado depois do cadastro.' : 'Apenas letras, números e hífens.'}
        errors={fieldErrors.sku}
      />
      <FormField id="name" label="Nome" defaultValue={product?.name} errors={fieldErrors.name} />
      <FormField
        id="priceInCents"
        label="Preço (R$)"
        inputMode="decimal"
        defaultValue={product ? centsToInput(product.priceInCents) : undefined}
        hint="Exemplo: 49,90"
        errors={fieldErrors.priceInCents}
      />
      <FormField
        id="description"
        label="Descrição"
        multiline
        required={false}
        defaultValue={product?.description ?? undefined}
        errors={fieldErrors.description}
      />

      {error && (
        <p role="alert" className="text-sm text-destructive">
          {error}
        </p>
      )}

      <Button type="submit" disabled={submitting}>
        {submitting ? 'Salvando...' : submitLabel}
      </Button>
    </form>
  )
}
