import { Component, inject, signal, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService, SupportedLanguage } from '@core/services/language.service';
import { CurrencyService, SupportedCurrency } from '@core/services/currency.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { CartStore } from '@features/storefront/cart.store';
import { ModalService } from '@core/services/modal.service';
import { CartModalComponent } from '@features/storefront/cart-modal/cart-modal.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    CommonModule,
    IconComponent,
    RouterModule,
    LogoComponent,
    BadgeComponent,
    TranslatePipe
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
  public langService = inject(LanguageService);
  public currencyService = inject(CurrencyService);
  public router = inject(Router);
  public cart = inject(CartStore);
  private modalService = inject(ModalService);

  public showUserDropdown = signal(false);
  public showLangDropdown = signal(false);
  public showCurrencyDropdown = signal(false);

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
    this.showLangDropdown.set(false);
    this.showCurrencyDropdown.set(false);
    this.showUserDropdown.update(v => !v);
  }

  public toggleLangDropdown(event: Event) {
    event.stopPropagation();
    this.showUserDropdown.set(false);
    this.showCurrencyDropdown.set(false);
    this.showLangDropdown.update(v => !v);
  }

  public toggleCurrencyDropdown(event: Event) {
    event.stopPropagation();
    this.showUserDropdown.set(false);
    this.showLangDropdown.set(false);
    this.showCurrencyDropdown.update(v => !v);
  }

  public selectLang(lang: SupportedLanguage) {
    this.langService.setLanguage(lang);
    this.showLangDropdown.set(false);
  }

  public selectCurrency(curr: SupportedCurrency) {
    this.currencyService.setCurrency(curr);
    this.showCurrencyDropdown.set(false);
  }

  @HostListener('document:click')
  public closeDropdowns() {
    this.showUserDropdown.set(false);
    this.showLangDropdown.set(false);
    this.showCurrencyDropdown.set(false);
  }

  public openCart() {
    this.modalService.open(CartModalComponent, {
      title: this.langService.translate('cart.title'),
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
      title: this.langService.translate('nav.logout'),
      description: this.langService.translate('header.logout_confirm'),
      confirmText: this.langService.translate('nav.logout'),
      cancelText: this.langService.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.stateService.authStore.logout().subscribe();
      }
    });
  }

  public formatBalance(balance: number | undefined): string {
    return this.currencyService.format(balance || 0);
  }
}

