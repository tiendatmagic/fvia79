import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';
import { LanguageService } from '../services/language.service';
import { map } from 'rxjs/operators';

export const AdminGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);
  const lang = inject(LanguageService);

  if (stateService.isAdmin()) {
    return true;
  }

  return stateService.authStore.checkMe(false, true).pipe(
    map(user => {
      if (user && user.role === 'admin') {
        return true;
      }
      stateService.showToast(lang.translate('toast.admin_required'), 'error');
      router.navigate(['/storefront']);
      return false;
    })
  );
};
