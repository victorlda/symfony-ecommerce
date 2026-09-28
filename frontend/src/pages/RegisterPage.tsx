import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router'
import { api } from '@/api/client'
import { ApiError, unwrap } from '@/api/problem'
import { useAuth } from '@/auth/auth-context'
import { FormField } from '@/components/FormField'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

export function RegisterPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    const form = new FormData(event.currentTarget)
    const body = {
      name: String(form.get('name')),
      email: String(form.get('email')),
      password: String(form.get('password')),
    }

    setSubmitting(true)
    setFieldErrors({})
    setError(null)

    try {
      await unwrap(api.POST('/api/users', { body }))
      await login(body.email, body.password)
      void navigate('/', { replace: true })
    } catch (e) {
      if (e instanceof ApiError && e.problem.code === 'validation_failed') {
        setFieldErrors(e.fieldErrors())
      } else {
        setError(e instanceof ApiError ? e.problem.detail : 'Não foi possível criar a conta. Tente novamente.')
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Card className="mx-auto max-w-sm">
      <CardHeader>
        <CardTitle>Criar conta</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <FormField id="name" label="Nome" autoComplete="name" errors={fieldErrors.name} />
          <FormField id="email" label="E-mail" type="email" autoComplete="email" errors={fieldErrors.email} />
          <FormField
            id="password"
            label="Senha"
            type="password"
            autoComplete="new-password"
            errors={fieldErrors.password}
          />

          {error && (
            <p role="alert" className="text-sm text-destructive">
              {error}
            </p>
          )}

          <Button type="submit" className="w-full" disabled={submitting}>
            {submitting ? 'Criando conta...' : 'Criar conta'}
          </Button>

          <p className="text-center text-sm text-muted-foreground">
            Já tem conta?{' '}
            <Link to="/entrar" className="underline">
              Entrar
            </Link>
          </p>
        </form>
      </CardContent>
    </Card>
  )
}
