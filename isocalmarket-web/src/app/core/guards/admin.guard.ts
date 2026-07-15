import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';
import { map } from 'rxjs/operators';
import { of } from 'rxjs';

export const AdminGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);

  // Kiểm tra vai trò admin
  if (stateService.isAdmin()) {
    return true;
  }

  // Nếu có cookie logged in, chờ checkMe hoàn thành để kiểm tra vai trò
  const isLocalLoggedIn = typeof document !== 'undefined' && document.cookie.includes('auth_logged_in=true');
  if (isLocalLoggedIn) {
    return stateService.authStore.checkMe().pipe(
      map(user => {
        if (user && user.role === 'admin') {
          return true;
        }
        stateService.showToast('Bạn không có quyền truy cập trang quản trị!', 'error');
        router.navigate(['/storefront']);
        return false;
      })
    );
  }

  // Nếu không phải admin và không có session, chuyển về trang storefront
  stateService.showToast('Bạn không có quyền truy cập trang quản trị!', 'error');
  router.navigate(['/storefront']);
  return of(false);
};
