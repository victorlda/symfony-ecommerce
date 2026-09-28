import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useEffect, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { api } from '@/api/client'
import { unwrap } from '@/api/problem'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { Product } from '@/features/products/ProductForm'
import { formatPrice } from '@/lib/format'

type Status = 'all' | 'active' | 'archived'

const STATUS_OPTIONS: { value: Status; label: string }[] = [
  { value: 'all', label: 'Todos' },
  { value: 'active', label: 'Ativos' },
  { value: 'archived', label: 'Arquivados' },
]

function parseStatus(value: string | null): Status {
  return STATUS_OPTIONS.some((option) => option.value === value) ? (value as Status) : 'all'
}

export function AdminProductsPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [params, setParams] = useSearchParams()

  // Os filtros vivem na URL: dá para compartilhar o link, e o botão "voltar" funciona.
  const status = parseStatus(params.get('status'))
  const q = params.get('q') ?? ''
  const page = Math.max(1, Number(params.get('page')) || 1)
  const [search, setSearch] = useState(q)

  function updateParams(changes: Record<string, string | null>) {
    setParams((current) => {
      const next = new URLSearchParams(current)
      for (const [key, value] of Object.entries(changes)) {
        if (value === null || value === '') {
          next.delete(key)
        } else {
          next.set(key, value)
        }
      }
      return next
    })
  }

  // Espera o admin parar de digitar por 300 ms antes de buscar.
  useEffect(() => {
    if (search === q) {
      return
    }

    const timeout = setTimeout(() => updateParams({ q: search, page: null }), 300)
    return () => clearTimeout(timeout)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search, q])

  const { data, isPending, isError } = useQuery({
    queryKey: ['admin-products', { status, q, page }],
    queryFn: () =>
      unwrap(
        api.GET('/api/admin/products', {
          params: { query: { status, q: q || undefined, page, limit: 20 } },
        }),
      ),
    placeholderData: keepPreviousData,
  })

  const toggleStatus = useMutation({
    mutationFn: (product: Product) =>
      product.active
        ? unwrap(api.DELETE('/api/products/{id}', { params: { path: { id: product.id } } }))
        : unwrap(api.POST('/api/products/{id}/restore', { params: { path: { id: product.id } } })),
    onSuccess: () =>
      Promise.all([
        queryClient.invalidateQueries({ queryKey: ['admin-products'] }),
        queryClient.invalidateQueries({ queryKey: ['products'] }),
      ]),
  })

  function handleToggle(product: Product) {
    if (product.active && !window.confirm(`Arquivar "${product.name}"? Ele deixará de aparecer na loja.`)) {
      return
    }
    toggleStatus.mutate(product)
  }

  return (
    <>
      <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-3xl font-bold">Produtos</h1>
        <Button onClick={() => void navigate('/admin/produtos/novo')}>Novo produto</Button>
      </div>

      <div className="mb-4 flex flex-wrap items-center gap-2">
        <Input
          type="search"
          placeholder="Buscar por nome ou SKU"
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          className="max-w-xs"
          aria-label="Buscar produtos"
        />
        {STATUS_OPTIONS.map((option) => (
          <Button
            key={option.value}
            size="sm"
            variant={status === option.value ? 'default' : 'outline'}
            onClick={() => updateParams({ status: option.value === 'all' ? null : option.value, page: null })}
          >
            {option.label}
          </Button>
        ))}
      </div>

      {isPending && <p className="text-muted-foreground">Carregando produtos...</p>}
      {isError && <p className="text-destructive">Não foi possível carregar os produtos.</p>}
      {toggleStatus.isError && <p className="mb-4 text-destructive">Não foi possível alterar o status do produto.</p>}

      {data && (
        <>
          <div className="overflow-x-auto rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>SKU</TableHead>
                  <TableHead>Nome</TableHead>
                  <TableHead className="text-right">Preço</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.data.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={5} className="text-center text-muted-foreground">
                      Nenhum produto encontrado.
                    </TableCell>
                  </TableRow>
                )}
                {data.data.map((product) => {
                  const busy = toggleStatus.isPending && toggleStatus.variables?.id === product.id

                  return (
                    <TableRow key={product.id}>
                      <TableCell className="font-mono text-sm">{product.sku}</TableCell>
                      <TableCell>{product.name}</TableCell>
                      <TableCell className="text-right">{formatPrice(product.priceInCents)}</TableCell>
                      <TableCell>
                        <Badge variant={product.active ? 'default' : 'secondary'}>
                          {product.active ? 'Ativo' : 'Arquivado'}
                        </Badge>
                      </TableCell>
                      <TableCell className="space-x-2 text-right">
                        <Link to={`/admin/produtos/${product.id}`} className="text-sm underline">
                          Editar
                        </Link>
                        <Button size="sm" variant="outline" disabled={busy} onClick={() => handleToggle(product)}>
                          {product.active ? 'Arquivar' : 'Reativar'}
                        </Button>
                      </TableCell>
                    </TableRow>
                  )
                })}
              </TableBody>
            </Table>
          </div>

          <div className="mt-4 flex items-center justify-between text-sm text-muted-foreground">
            <span>
              {data.meta.total} produto(s) · página {data.meta.page} de {Math.max(1, data.meta.totalPages)}
            </span>
            <div className="space-x-2">
              <Button
                size="sm"
                variant="outline"
                disabled={page <= 1}
                onClick={() => updateParams({ page: String(page - 1) })}
              >
                Anterior
              </Button>
              <Button
                size="sm"
                variant="outline"
                disabled={page >= data.meta.totalPages}
                onClick={() => updateParams({ page: String(page + 1) })}
              >
                Próxima
              </Button>
            </div>
          </div>
        </>
      )}
    </>
  )
}
