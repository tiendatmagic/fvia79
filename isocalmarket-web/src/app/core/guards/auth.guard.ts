import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';
import { LanguageService } from '../services/language.service';
import { map } from 'rxjs/operators';
import { of } from 'rxjs';

export const AuthGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);
  const langService = inject(LanguageService);

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
        stateService.showToast(langService.translate('common.please_login'), 'warning');
        router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
        return false;
      })
    );
  }

  stateService.showToast(langService.translate('common.please_login'), 'warning');
  router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
  return of(false);
};
