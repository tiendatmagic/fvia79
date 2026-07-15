import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { StateService } from '../services/state.service';

export const AdminGuard: CanActivateFn = (route, state) => {
  const stateService = inject(StateService);
  const router = inject(Router);

  // Kiểm tra vai trò admin
  if (stateService.isAdmin && stateService.isAdmin()) {
    return true;
  }

  // Nếu không phải admin, chuyển về trang storefront
  stateService.showToast('Bạn không có quyền truy cập trang quản trị!', 'error');
  router.navigate(['/storefront']);
  return false;
};
