import { createBrowserRouter } from 'react-router'
import { RequireAdmin } from '@/auth/RequireAdmin'
import { RootLayout } from '@/layouts/RootLayout'
import { AdminPage } from '@/pages/AdminPage'
import { CatalogPage } from '@/pages/CatalogPage'
import { LoginPage } from '@/pages/LoginPage'
import { RegisterPage } from '@/pages/RegisterPage'

export const router = createBrowserRouter([
  {
    path: '/',
    element: <RootLayout />,
    children: [
      { index: true, element: <CatalogPage /> },
      { path: 'entrar', element: <LoginPage /> },
      { path: 'cadastro', element: <RegisterPage /> },
      {
        element: <RequireAdmin />,
        children: [{ path: 'admin', element: <AdminPage /> }],
      },
    ],
  },
])
