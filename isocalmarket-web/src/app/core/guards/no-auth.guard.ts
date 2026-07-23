import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';
import { map } from 'rxjs/operators';
import { of } from 'rxjs';

export const NoAuthGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);

  if (stateService.isAuthenticated()) {
    const dest = stateService.isAdmin() ? '/admin/dashboard' : '/storefront';
    router.navigateByUrl(dest);
    return false;
  }

  if (stateService.authStore.hasLoggedInMarker()) {
    return stateService.authStore.checkMe().pipe(
      map(user => {
        if (user) {
          const dest = user.role === 'admin' ? '/admin/dashboard' : '/storefront';
          router.navigateByUrl(dest);
          return false;
        }
        return true;
      })
    );
  }

  return of(true);
};
