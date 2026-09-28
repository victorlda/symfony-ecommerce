const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

/** 4990 → "R$ 49,90" */
export function formatPrice(cents: number): string {
  return currency.format(cents / 100)
}

/** 4990 → "49,90" (para preencher campos de formulário) */
export function centsToInput(cents: number): string {
  return (cents / 100).toFixed(2).replace('.', ',')
}

/** "49,90" ou "49.90" → 4990. Valores inválidos viram 0, e a API responde com o erro de validação. */
export function parsePriceInput(value: string): number {
  const normalized = value.trim().replace(/\./g, '').replace(',', '.')
  const amount = Number.parseFloat(normalized)

  return Number.isFinite(amount) ? Math.round(amount * 100) : 0
}
