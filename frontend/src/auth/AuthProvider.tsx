import { useQuery, useQueryClient } from '@tanstack/react-query'
import { useCallback, useEffect, useMemo, useState, useSyncExternalStore, type ReactNode } from 'react'
import { api } from '@/api/client'
import { unwrap } from '@/api/problem'
import { AuthContext, type AuthStatus } from './auth-context'
import { refreshAccessToken } from './refresh'
import { tokenStore } from './token-store'

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()
  const token = useSyncExternalStore(tokenStore.subscribe, tokenStore.get)
  const [restoring, setRestoring] = useState(true)

  // Ao abrir ou recarregar a página, tenta restaurar a sessão pelo cookie do refresh token.
  useEffect(() => {
    void refreshAccessToken().finally(() => setRestoring(false))
  }, [])

  const me = useQuery({
    queryKey: ['me'],
    queryFn: () => unwrap(api.GET('/api/me')),
    enabled: token !== null,
    staleTime: Infinity,
    retry: false,
  })

  const login = useCallback(async (email: string, password: string) => {
    const data = await unwrap(api.POST('/api/login', { body: { email, password } }))

    if (!data.token) {
      throw new Error('Resposta de login inválida.')
    }

    tokenStore.set(data.token)
  }, [])

  const logout = useCallback(async () => {
    await api.POST('/api/auth/logout')
    tokenStore.set(null)
    queryClient.removeQueries({ queryKey: ['me'] })
  }, [queryClient])

  let status: AuthStatus = 'anonymous'
  if (restoring || (token !== null && me.isPending)) {
    status = 'loading'
  } else if (token !== null && me.data) {
    status = 'authenticated'
  }

  const value = useMemo(
    () => ({ status, user: status === 'authenticated' ? (me.data ?? null) : null, login, logout }),
    [status, me.data, login, logout],
  )

  return <AuthContext value={value}>{children}</AuthContext>
}
