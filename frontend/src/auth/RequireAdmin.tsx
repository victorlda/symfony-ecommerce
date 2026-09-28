import { Navigate, Outlet, useLocation } from 'react-router'
import { isAdmin, useAuth } from './auth-context'

/**
 * Protege rotas do painel. É uma proteção de experiência do usuário:
 * a segurança de verdade está na API, que valida o token e o perfil em cada requisição.
 */
export function RequireAdmin() {
  const { status, user } = useAuth()
  const location = useLocation()

  if (status === 'loading') {
    return <p className="text-muted-foreground">Carregando...</p>
  }

  if (status === 'anonymous') {
    return <Navigate to="/entrar" replace state={{ from: location.pathname }} />
  }

  if (!isAdmin(user)) {
    return <p>Você não tem permissão para acessar esta página.</p>
  }

  return <Outlet />
}
