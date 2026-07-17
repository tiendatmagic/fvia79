import { Component, inject, signal, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { CartStore } from '@features/storefront/cart.store';
import { ModalService } from '@core/services/modal.service';
import { CartModalComponent } from '@features/storefront/cart-modal/cart-modal.component';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    CommonModule,
    IconComponent,
    RouterModule,
    LogoComponent
  ],
  templateUrl: './header.component.html',
  styles: [
    `
      :host {
        display: contents;
      }
    `,
  ]
})
export class HeaderComponent {
  public stateService = inject(StateService);
  public router = inject(Router);
  public cart = inject(CartStore);
  private modalService = inject(ModalService);

  public showUserDropdown = signal(false);

  public openMobileMenu() {
    this.stateService.showMobileMenu.set(true);
  }

  public isAuthPage(): boolean {
    return this.router.url.includes('/auth/');
  }

  public isStorefrontPage(): boolean {
    return !this.router.url.includes('/admin') && !this.router.url.includes('/auth/');
  }

  public toggleUserDropdown(event: Event) {
    event.stopPropagation();
    this.showUserDropdown.update(v => !v);
  }

  @HostListener('document:click')
  public closeDropdown() {
    this.showUserDropdown.set(false);
  }

  public openCart() {
    this.modalService.open(CartModalComponent, {
      title: 'Giỏ hàng của bạn',
      size: 'lg'
    }).afterClosed$.subscribe((result) => {
      if (result === 'login') {
        this.router.navigate(['/login'], { queryParams: { returnUrl: '/storefront' } });
      } else if (result === 'success') {
        this.router.navigate(['/orders']);
      }
    });
  }
  public getUserInitial(): string {
    const name = this.stateService.currentUser()?.name || '';
    return name.charAt(0).toUpperCase();
  }

  public logout() {
    this.showUserDropdown.set(false);
    this.modalService.confirm({
      title: 'Xác nhận đăng xuất',
      description: 'Bạn có chắc chắn muốn đăng xuất tài khoản?',
      confirmText: 'Đăng xuất',
      cancelText: 'Hủy',
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.stateService.authStore.logout().subscribe();
      }
    });
  }

  public formatBalance(balance: number | undefined): string {
    if (!balance) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(balance);
  }
}
