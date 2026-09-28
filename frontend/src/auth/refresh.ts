import { tokenStore } from './token-store'

let inFlight: Promise<string | null> | null = null

/**
 * Renova o access token usando o cookie httpOnly do refresh token.
 *
 * Chamadas simultâneas compartilham a mesma requisição. Duas renovações em paralelo
 * reapresentariam um refresh token já rotacionado, e a API encerraria a sessão
 * por suspeita de roubo.
 */
export function refreshAccessToken(): Promise<string | null> {
  inFlight ??= requestNewToken().finally(() => {
    inFlight = null
  })

  return inFlight
}

async function requestNewToken(): Promise<string | null> {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/auth/refresh`, {
      method: 'POST',
      credentials: 'include',
    })

    if (!response.ok) {
      tokenStore.set(null)
      return null
    }

    const body = (await response.json()) as { token?: string }
    tokenStore.set(body.token ?? null)

    return body.token ?? null
  } catch {
    tokenStore.set(null)
    return null
  }
}
