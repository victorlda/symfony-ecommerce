import { Link, NavLink, Outlet, useNavigate } from 'react-router'
import { isAdmin, useAuth } from '@/auth/auth-context'
import { Button } from '@/components/ui/button'

export function RootLayout() {
  const { status, user, logout } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    void navigate('/')
  }

  return (
    <div className="min-h-screen">
      <header className="border-b">
        <div className="mx-auto flex max-w-5xl items-center justify-between p-4">
          <Link to="/" className="text-xl font-bold">
            E-commerce
          </Link>

          <nav className="flex items-center gap-4 text-sm">
            <NavLink to="/">Catálogo</NavLink>
            {isAdmin(user) && <NavLink to="/admin">Painel</NavLink>}

            {status === 'authenticated' && user && (
              <>
                <span className="text-muted-foreground">Olá, {user.name}</span>
                <Button variant="outline" size="sm" onClick={handleLogout}>
                  Sair
                </Button>
              </>
            )}

            {status === 'anonymous' && (
              <>
                <NavLink to="/entrar">Entrar</NavLink>
                <Button size="sm" onClick={() => void navigate('/cadastro')}>
                  Criar conta
                </Button>
              </>
            )}
          </nav>
        </div>
      </header>

      <main className="mx-auto max-w-5xl p-6">
        <Outlet />
      </main>
    </div>
  )
}
