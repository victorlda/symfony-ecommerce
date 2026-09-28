import { useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate } from 'react-router'
import { api } from '@/api/client'
import { unwrap } from '@/api/problem'
import { Card, CardContent } from '@/components/ui/card'
import { ProductForm } from '@/features/products/ProductForm'

export function AdminProductCreatePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  return (
    <>
      <Link to="/admin" className="text-sm text-muted-foreground underline">
        ← Voltar para produtos
      </Link>
      <h1 className="mt-2 mb-6 text-3xl font-bold">Novo produto</h1>

      <Card className="max-w-2xl">
        <CardContent>
          <ProductForm
            submitLabel="Cadastrar produto"
            onSubmit={async (values) => {
              await unwrap(api.POST('/api/products', { body: values }))
              await Promise.all([
                queryClient.invalidateQueries({ queryKey: ['admin-products'] }),
                queryClient.invalidateQueries({ queryKey: ['products'] }),
              ])
              void navigate('/admin')
            }}
          />
        </CardContent>
      </Card>
    </>
  )
}
