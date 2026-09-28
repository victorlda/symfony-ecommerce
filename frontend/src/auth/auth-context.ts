import { createContext, useContext } from 'react'
import type { components } from '@/api/schema'

export type User = components['schemas']['UserResponse']
export type AuthStatus = 'loading' | 'authenticated' | 'anonymous'

export type AuthContextValue = {
  status: AuthStatus
  user: User | null
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
}

export const AuthContext = createContext<AuthContextValue | null>(null)

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext)

  if (context === null) {
    throw new Error('useAuth deve ser usado dentro de <AuthProvider>.')
  }

  return context
}

export function isAdmin(user: User | null): boolean {
  return user?.roles.includes('ROLE_ADMIN') ?? false
}
