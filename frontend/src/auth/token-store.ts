/**
 * Guarda o access token apenas na memória.
 * Nunca em localStorage: qualquer script injetado na página (XSS) conseguiria lê-lo.
 */
let accessToken: string | null = null
const listeners = new Set<() => void>()

export const tokenStore = {
  get: (): string | null => accessToken,

  set(token: string | null): void {
    accessToken = token
    listeners.forEach((listener) => listener())
  },

  subscribe(listener: () => void): () => void {
    listeners.add(listener)
    return () => {
      listeners.delete(listener)
    }
  },
}
