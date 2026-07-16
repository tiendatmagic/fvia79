import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';
import { AdminGuard } from './core/guards/admin.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'storefront', pathMatch: 'full' },
  {
    path: 'storefront',
    loadComponent: () => import('@features/storefront/storefront.component').then(m => m.StorefrontComponent)
  },
  {
    path: 'auth',
    loadChildren: () => import('@features/auth/auth.routes').then(m => m.AUTH_ROUTES)
  },
  {
    path: 'orders',
    loadComponent: () => import('@features/orders/orders.component').then(m => m.OrdersComponent),
    canActivate: [AuthGuard]
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

