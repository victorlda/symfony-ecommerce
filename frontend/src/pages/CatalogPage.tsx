import { useQuery } from '@tanstack/react-query'
import { api } from '@/api/client'
import { unwrap } from '@/api/problem'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const formatPrice = (cents: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(cents / 100)

export function CatalogPage() {
  const { data, isPending, isError } = useQuery({
    queryKey: ['products', { page: 1 }],
    queryFn: () => unwrap(api.GET('/api/products', { params: { query: { page: 1, limit: 20 } } })),
  })

  return (
    <>
      <h1 className="mb-6 text-3xl font-bold">Catálogo</h1>

      {isPending && <p className="text-muted-foreground">Carregando produtos...</p>}
      {isError && <p className="text-destructive">Não foi possível carregar os produtos.</p>}

      {data && (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {data.data.map((product) => (
            <Card key={product.id}>
              <CardHeader>
                <CardTitle>{product.name}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <p className="text-sm text-muted-foreground">{product.description}</p>
                <p className="text-lg font-semibold">{formatPrice(product.priceInCents)}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </>
  )
}
