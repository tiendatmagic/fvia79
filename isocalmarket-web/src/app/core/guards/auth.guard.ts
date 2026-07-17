import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';
import { map } from 'rxjs/operators';
import { of } from 'rxjs';

export const AuthGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);

  if (stateService.isAuthenticated()) {
    return true;
  }

  const isLocalLoggedIn = typeof document !== 'undefined' && document.cookie.includes('auth_logged_in=true');
  if (isLocalLoggedIn) {
    return stateService.authStore.checkMe().pipe(
      map(user => {
        if (user) {
          return true;
        }
        router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
        return false;
      })
    );
  }

  router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
  return of(false);
};
