import createClient from 'openapi-fetch'
import { refreshAccessToken } from '@/auth/refresh'
import { tokenStore } from '@/auth/token-store'
import type { paths } from './schema'

export const api = createClient<paths>({
  baseUrl: import.meta.env.VITE_API_URL,
  credentials: 'include',
  fetch: authenticatedFetch,
})

/**
 * Anexa o access token e, se ele tiver expirado, renova e repete a requisição uma única vez.
 */
async function authenticatedFetch(request: Request): Promise<Response> {
  // O corpo de uma requisição só pode ser lido uma vez; guardamos uma cópia para a repetição.
  const retry = request.clone()
  const response = await fetch(withToken(request, tokenStore.get()))

  if (response.status !== 401 || !(await isTokenExpired(response))) {
    return response
  }

  const token = await refreshAccessToken()

  return token ? fetch(withToken(retry, token)) : response
}

function withToken(request: Request, token: string | null): Request {
  if (token === null) {
    return request
  }

  const headers = new Headers(request.headers)
  headers.set('Authorization', `Bearer ${token}`)

  return new Request(request, { headers })
}

async function isTokenExpired(response: Response): Promise<boolean> {
  const body: unknown = await response
    .clone()
    .json()
    .catch(() => null)

  return typeof body === 'object' && body !== null && 'code' in body && body.code === 'token_expired'
}
