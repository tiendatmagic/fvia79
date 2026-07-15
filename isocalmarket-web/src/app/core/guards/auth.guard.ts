import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';

export const AuthGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);

  // Nếu đã đăng nhập, cho phép truy cập
  if (stateService.isAuthenticated && stateService.isAuthenticated()) {
    return true;
  }

  // Nếu chưa đăng nhập, chuyển hướng sang màn hình login
  router.navigate(['/auth/login'], { queryParams: { returnUrl: state.url } });
  return false;
};
