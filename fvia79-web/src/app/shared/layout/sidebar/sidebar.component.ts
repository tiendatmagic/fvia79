import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { PublicSettingStore } from '@core/services/public-setting.store';
import { LanguageService } from '@core/services/language.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    IconComponent,
    LogoComponent,
    ThemeSwitcherComponent,
    TranslatePipe
  ],
  templateUrl: './sidebar.component.html',
  styles: [
    `
      :host {
        display: contents;
      }
    `,
  ],
})
export class SidebarComponent implements OnInit {
  public stateService = inject(StateService);
  public publicSettingStore = inject(PublicSettingStore);
  public lang = inject(LanguageService);

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

  ngOnInit() {
    this.publicSettingStore.fetchPublicSettings().subscribe();
    this.stateService.productStore.fetchCategories().subscribe();
  }

  public toggleBuyMenu() {
    this.isBuyMenuOpen.update((v) => !v);
  }

  public getCategoryName(cat: any): string {
    const currentLang = this.lang.currentLang();
    return (currentLang === 'vi' ? (cat.name_vi || cat.name_en || cat.name) : (cat.name_en || cat.name_vi || cat.name)) || '';
  }
}
