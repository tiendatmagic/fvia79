import { Routes } from '@angular/router';

export const PROFILE_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () => import('./profile.component').then(m => m.ProfileComponent),
    children: [
      { path: '', redirectTo: 'information', pathMatch: 'full' },
      {
        path: 'information',
        loadComponent: () => import('./information/profile-information.component').then(m => m.ProfileInformationComponent)
      },
      {
        path: 'password',
        loadComponent: () => import('./password/profile-password.component').then(m => m.ProfilePasswordComponent)
      },
      {
        path: 'security',
        loadComponent: () => import('./security/profile-security.component').then(m => m.ProfileSecurityComponent)
      },
      {
        path: 'devices',
        loadComponent: () => import('./devices/profile-devices.component').then(m => m.ProfileDevicesComponent)
      }
    ]
  }
];
