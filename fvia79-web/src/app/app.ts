import { Component, inject, OnInit, effect, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterModule, Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { HeaderComponent } from '@shared/layout/header/header.component';
import { ToastComponent } from '@shared/components/toast/toast.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SidebarComponent } from '@shared/layout/sidebar/sidebar.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
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
export class App implements OnInit {
  public stateService = inject(StateService);
  public publicSettingStore = inject(PublicSettingStore);
  public lang = inject(LanguageService);
  private router = inject(Router);
  public hasBeenOpened = signal<boolean>(false);
  public activeMenuState = signal<boolean>(false);
  public isAuthPage = signal<boolean>(
    typeof window !== 'undefined' && 
    (window.location.pathname.includes('/login') || window.location.pathname.includes('/register'))
  );

  public isBuyMenuOpen = signal<boolean>(false);

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
    this.stateService.authStore.checkMe(false, true).subscribe();
    this.stateService.productStore.fetchCategories().subscribe();

    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: any) => {
      this.stateService.authStore.checkMe().subscribe();
      const url = event.urlAfterRedirects || event.url || '';
      this.isAuthPage.set(url.includes('/login') || url.includes('/register'));
    });
  }

  public toggleBuyMenu() {
    this.isBuyMenuOpen.update((v) => !v);
  }

  public getCategoryName(cat: any): string {
    const currentLang = this.lang.currentLang();
    return (currentLang === 'vi' ? (cat.name_vi || cat.name_en || cat.name) : (cat.name_en || cat.name_vi || cat.name)) || '';
  }
}

