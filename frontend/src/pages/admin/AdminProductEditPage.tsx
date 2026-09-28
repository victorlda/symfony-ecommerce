import { useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate, useParams } from 'react-router'
import { api } from '@/api/client'
import { unwrap } from '@/api/problem'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { ProductForm } from '@/features/products/ProductForm'

export function AdminProductEditPage() {
  const { id = '' } = useParams()
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const { data: product, isPending, isError } = useQuery({
    queryKey: ['admin-product', id],
    queryFn: () => unwrap(api.GET('/api/admin/products/{id}', { params: { path: { id } } })),
  })

  return (
    <>
      <Link to="/admin" className="text-sm text-muted-foreground underline">
        ← Voltar para produtos
      </Link>

      {isPending && <p className="mt-4 text-muted-foreground">Carregando produto...</p>}
      {isError && <p className="mt-4 text-destructive">Produto não encontrado.</p>}

      {product && (
        <>
          <div className="mt-2 mb-6 flex items-center gap-3">
            <h1 className="text-3xl font-bold">Editar produto</h1>
            <Badge variant={product.active ? 'default' : 'secondary'}>
              {product.active ? 'Ativo' : 'Arquivado'}
            </Badge>
          </div>

          <Card className="max-w-2xl">
            <CardContent>
              <ProductForm
                product={product}
                submitLabel="Salvar alterações"
                onSubmit={async ({ name, priceInCents, description }) => {
                  await unwrap(
                    api.PUT('/api/products/{id}', {
                      params: { path: { id } },
                      body: { name, priceInCents, description },
                    }),
                  )
                  await Promise.all([
                    queryClient.invalidateQueries({ queryKey: ['admin-products'] }),
                    queryClient.invalidateQueries({ queryKey: ['admin-product', id] }),
                    queryClient.invalidateQueries({ queryKey: ['products'] }),
                  ])
                  void navigate('/admin')
                }}
              />
            </CardContent>
          </Card>
        </>
      )}
    </>
  )
}
