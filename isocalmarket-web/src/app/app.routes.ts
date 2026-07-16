import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';
import { AdminGuard } from './core/guards/admin.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'storefront', pathMatch: 'full' },
  {
    path: 'storefront',
    loadComponent: () => import('@features/storefront/storefront.component').then(m => m.StorefrontComponent)
  },

  // Auth — route ngắn gọn: /login, /register
  { path: 'auth', redirectTo: 'login', pathMatch: 'full' },
  { path: 'auth/login', redirectTo: 'login', pathMatch: 'full' },
  { path: 'auth/register', redirectTo: 'register', pathMatch: 'full' },
  {
    path: 'login',
    loadComponent: () => import('@features/auth/login.component').then(m => m.LoginComponent)
  },
  {
    path: 'register',
    loadComponent: () => import('@features/auth/register.component').then(m => m.RegisterComponent)
  },

  {
    path: 'orders',
    canActivate: [AuthGuard],
    children: [
      {
        path: '',
        loadComponent: () => import('@features/orders/orders.component').then(m => m.OrdersComponent)
      },
      {
        path: ':id',
        loadComponent: () => import('@features/orders/order-detail/order-detail.component').then(m => m.OrderDetailComponent)
      }
    ]
  },
  {
    path: 'profile',
    loadChildren: () => import('@features/profile/profile.routes').then(m => m.PROFILE_ROUTES),
    canActivate: [AuthGuard]
  },
  {
    path: 'admin',
    canActivate: [AuthGuard, AdminGuard],
    children: [
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      {
        path: 'dashboard',
        loadComponent: () => import('@features/admin-dashboard/admin-dashboard.component').then(m => m.AdminDashboardComponent)
      },
      {
        path: 'products',
        loadComponent: () => import('@features/products/products.component').then(m => m.ProductsComponent)
      },
      {
        path: 'inventory',
        loadComponent: () => import('@features/inventory/inventory.component').then(m => m.InventoryComponent)
      },
      {
        path: 'orders',
        loadComponent: () => import('@features/admin-orders/admin-orders.component').then(m => m.AdminOrdersComponent)
      }
    ]
  },
  { path: '**', redirectTo: 'storefront' }
];
