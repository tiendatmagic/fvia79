import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';
import { AdminGuard } from './core/guards/admin.guard';
import { NoAuthGuard } from './core/guards/no-auth.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'storefront', pathMatch: 'full' },
  {
    path: 'storefront',
    loadComponent: () => import('@features/storefront/storefront.component').then(m => m.StorefrontComponent)
  },

  { path: 'auth', redirectTo: 'login', pathMatch: 'full' },
  { path: 'auth/login', redirectTo: 'login', pathMatch: 'full' },
  { path: 'auth/register', redirectTo: 'register', pathMatch: 'full' },
  {
    path: 'login',
    canActivate: [NoAuthGuard],
    loadComponent: () => import('@features/auth/auth-layout.component').then(m => m.AuthLayoutComponent)
  },
  {
    path: 'register',
    canActivate: [NoAuthGuard],
    loadComponent: () => import('@features/auth/auth-layout.component').then(m => m.AuthLayoutComponent)
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
    path: 'user-api',
    loadComponent: () => import('@features/user-api/user-api.component').then(m => m.UserApiComponent),
    canActivate: [AuthGuard]
  },
  {
    path: 'deposit',
    canActivate: [AuthGuard],
    children: [
      {
        path: '',
        loadComponent: () => import('@features/deposit/deposit.component').then(m => m.DepositComponent)
      },
      {
        path: 'history',
        loadComponent: () => import('@features/deposit-history/deposit-history.component').then(m => m.DepositHistoryComponent)
      },
      {
        path: 'invoice/:id',
        loadComponent: () => import('@features/deposit-invoice/deposit-invoice.component').then(m => m.DepositInvoiceComponent)
      }
    ]
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
        path: 'categories',
        loadComponent: () => import('@features/admin-categories/admin-categories.component').then(m => m.AdminCategoriesComponent)
      },
      {
        path: 'coupons',
        loadComponent: () => import('@features/admin-coupons/admin-coupons.component').then(m => m.AdminCouponsComponent)
      },
      {
        path: 'inventory',
        loadComponent: () => import('@features/inventory/inventory.component').then(m => m.InventoryComponent)
      },
      {
        path: 'orders',
        loadComponent: () => import('@features/admin-orders/admin-orders.component').then(m => m.AdminOrdersComponent)
      },
      {
        path: 'users',
        loadComponent: () => import('@features/admin-users/admin-users.component').then(m => m.AdminUsersComponent)
      },
      {
        path: 'suppliers',
        loadComponent: () => import('@features/admin-suppliers/admin-suppliers.component').then(m => m.AdminSuppliersComponent)
      },
      { path: 'settings', redirectTo: 'settings/general', pathMatch: 'full' },
      { path: 'settings/suppliers', redirectTo: 'suppliers', pathMatch: 'full' },
      {
        path: 'settings/:tab',
        loadComponent: () => import('@features/admin-settings/admin-settings.component').then(m => m.AdminSettingsComponent)
      },
      {
        path: 'deposits',
        loadComponent: () => import('@features/admin-deposits/admin-deposits.component').then(m => m.AdminDepositsComponent)
      },
      {
        path: 'api',
        loadComponent: () => import('@features/admin-api/admin-api.component').then(m => m.AdminApiComponent)
      }
    ]
  },
  { path: '**', redirectTo: 'storefront' }
];
