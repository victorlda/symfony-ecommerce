import { createBrowserRouter } from 'react-router'
import { RequireAdmin } from '@/auth/RequireAdmin'
import { RootLayout } from '@/layouts/RootLayout'
import { AdminProductCreatePage } from '@/pages/admin/AdminProductCreatePage'
import { AdminProductEditPage } from '@/pages/admin/AdminProductEditPage'
import { AdminProductsPage } from '@/pages/admin/AdminProductsPage'
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
        path: 'admin',
        element: <RequireAdmin />,
        children: [
          { index: true, element: <AdminProductsPage /> },
          { path: 'produtos/novo', element: <AdminProductCreatePage /> },
          { path: 'produtos/:id', element: <AdminProductEditPage /> },
        ],
      },
    ],
  },
])
