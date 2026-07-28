import { Component, inject, OnInit, OnDestroy, effect, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterModule, Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Subscription, timer } from 'rxjs';
import { HeaderComponent } from '@shared/layout/header/header.component';
import { ToastComponent } from '@shared/components/toast/toast.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SidebarComponent } from '@shared/layout/sidebar/sidebar.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ModalService } from '@core/services/modal.service';
import { MaintenanceComponent } from '@features/maintenance/maintenance.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

import { PublicSettingStore } from '@core/services/public-setting.store';

@Component({
  selector: 'app-root',
  
  imports: [
    CommonModule,
    RouterOutlet,
    RouterModule,
    HeaderComponent,
    SidebarComponent,
    ToastComponent,
    IconComponent,
    LogoComponent,
    ThemeSwitcherComponent,
    MaintenanceComponent,
    TranslatePipe
  ],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App implements OnInit, OnDestroy {
  public stateService = inject(StateService);
  public publicSettingStore = inject(PublicSettingStore);
  public lang = inject(LanguageService);
  private modalService = inject(ModalService);
  private router = inject(Router);
  public hasBeenOpened = signal<boolean>(false);
  public activeMenuState = signal<boolean>(false);
  public isAuthPage = signal<boolean>(
    typeof window !== 'undefined' && 
    (window.location.pathname.includes('/login') || window.location.pathname.includes('/register'))
  );

  public isBuyMenuOpen = signal<boolean>(false);
  private isCheckingUnseenDeposit = false;
  private hasDepositCheckFailed = false;
  private notifiedDepositIds = new Set<string>();
  private unseenDepositTimerSub: Subscription | null = null;
  private visibilityHandler = () => {
    if (typeof document !== 'undefined' && !document.hidden) {
      this.checkUnseenDeposit();
    }
  };

  public readonly sortedCategories = computed(() => {
    const cats = (this.stateService.productStore.categories() || [])
      .filter((c: any) => c.is_active !== false && c.is_active !== 0 && c.is_active !== '0');
    const currentLang = this.lang.currentLang();
    return [...cats].sort((a, b) => {
      const nameA = (currentLang === 'vi' ? (a.name_vi || a.name_en || a.name) : (a.name_en || a.name_vi || a.name)) || '';
      const nameB = (currentLang === 'vi' ? (b.name_vi || b.name_en || b.name) : (b.name_en || b.name_vi || b.name)) || '';
      return nameA.localeCompare(nameB, currentLang === 'vi' ? 'vi' : 'en', { sensitivity: 'base' });
    });
  });

  constructor() {
    effect(() => {
      const open = this.stateService.showMobileMenu();
      if (open) {
        this.hasBeenOpened.set(true);
        setTimeout(() => {
          this.activeMenuState.set(true);
        }, 50);
      } else {
        this.activeMenuState.set(false);
        setTimeout(() => {
          if (!this.stateService.showMobileMenu()) {
            this.hasBeenOpened.set(false);
          }
        }, 300);
      }
    });
  }

  ngOnInit() {
    this.publicSettingStore.fetchPublicSettings().subscribe();
    this.stateService.authStore.checkMe().subscribe({
      next: (user) => {
        if (user) {
          this.checkUnseenDeposit();
        }
      }
    });

    // Check periodically every 30 seconds ngầm
    this.unseenDepositTimerSub = timer(15000, 30000).subscribe(() => {
      if (typeof document !== 'undefined' && !document.hidden) {
        this.checkUnseenDeposit();
      }
    });

    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', this.visibilityHandler);
    }

    if (!this.isAuthPage()) {
      this.stateService.productStore.fetchCategories().subscribe();
    }

    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: any) => {
      const url = event.urlAfterRedirects || event.url || '';
      const authPage = url.includes('/login') || url.includes('/register');
      this.isAuthPage.set(authPage);
      if (!authPage && this.stateService.productStore.categories().length === 0) {
        this.stateService.productStore.fetchCategories().subscribe();
      }
      this.checkUnseenDeposit();
    });
  }

  ngOnDestroy() {
    if (this.unseenDepositTimerSub) {
      this.unseenDepositTimerSub.unsubscribe();
      this.unseenDepositTimerSub = null;
    }
    if (typeof document !== 'undefined') {
      document.removeEventListener('visibilitychange', this.visibilityHandler);
    }
  }

  private isDepositModalOpen = false;

  public checkUnseenDeposit() {
    if (!this.stateService.authStore.currentUser() || this.isCheckingUnseenDeposit || this.hasDepositCheckFailed || this.isDepositModalOpen) return;
    
    // Nếu người dùng không tạo / không có lệnh nạp nào thì đừng gọi API unseen-success
    if (typeof localStorage !== 'undefined' && !localStorage.getItem('has_pending_deposit')) {
      return;
    }

    this.isCheckingUnseenDeposit = true;
    this.stateService.depositStore.checkUnseenSuccessDeposit().subscribe({
      next: (res) => {
        this.isCheckingUnseenDeposit = false;
        if (res && res.deposit) {
          const deposit: any = res.deposit;
          if (typeof localStorage !== 'undefined') {
            localStorage.removeItem('has_pending_deposit');
          }

          if (this.notifiedDepositIds.has(deposit.id)) return;
          this.notifiedDepositIds.add(deposit.id);

          this.stateService.depositStore.markDepositSeen(deposit.id).subscribe();
          this.stateService.authStore.checkMe(true).subscribe();

          const amountVal = deposit.amountUsdt || deposit.amountToPay || deposit.amount_usdt || deposit.amount_to_pay || '';
          const amountDisplay = amountVal ? `${amountVal}` : '';

          this.isDepositModalOpen = true;
          this.modalService.confirm({
            title: this.lang.translate('modal.deposit_success_title'),
            variant: 'success',
            description: this.lang.translate('modal.deposit_success_desc', { amount: amountDisplay }),
            confirmText: this.lang.translate('modal.deposit_success_confirm'),
            cancelText: this.lang.translate('common.close'),
            confirmButtonClass: 'btn-primary'
          }).afterClosed$.subscribe((viewHistory?: boolean) => {
            this.isDepositModalOpen = false;
            if (viewHistory) {
              this.router.navigate(['/deposit/history']);
            }
          });
        } else {
          // Nếu không còn đơn completed chưa báo và người dùng không ở trang hóa đơn nạp thì dọn flag
          if (typeof window !== 'undefined' && !window.location.pathname.includes('/deposit/invoice')) {
            if (typeof localStorage !== 'undefined') {
              localStorage.removeItem('has_pending_deposit');
            }
          }
        }
      },
      error: (err) => {
        this.isCheckingUnseenDeposit = false;
        this.hasDepositCheckFailed = true;
      }
    });
  }

  public toggleBuyMenu() {
    if (!this.stateService.isAuthenticated()) {
      this.stateService.showToast(this.lang.translate('common.please_login'), 'warning');
      this.router.navigate(['/login'], { queryParams: { returnUrl: '/storefront' } });
      if (this.stateService.showMobileMenu()) {
        this.stateService.showMobileMenu.set(false);
      }
      return;
    }
    this.isBuyMenuOpen.update((v) => !v);
  }

  public getCategoryName(cat: any): string {
    const currentLang = this.lang.currentLang();
    return (currentLang === 'vi' ? (cat.name_vi || cat.name_en || cat.name) : (cat.name_en || cat.name_vi || cat.name)) || '';
  }
}

