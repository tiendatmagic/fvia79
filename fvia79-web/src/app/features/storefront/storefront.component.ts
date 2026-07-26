import {
  Component,
  OnInit,
  inject,
  signal,
  computed,
  ChangeDetectionStrategy,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { CartStore } from './cart.store';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ModalService } from '@core/services/modal.service';
import { CartModalComponent } from './cart-modal/cart-modal.component';
import {
  TableComponent,
  TableCellDirective,
  TableColumn,
} from '@shared/components/table/table.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';

import { PublicSettingStore } from '@core/services/public-setting.store';

@Component({
  selector: 'app-storefront',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    SkeletonLoaderComponent,
    CustomSearchInputComponent,
    CustomSwitchComponent,
    CustomSelectComponent,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    IconComponent,
    PaginationComponent,
    TableComponent,
    TableCellDirective,
    TooltipDirective,
    TranslatePipe,
    CurrencyPipe,
  ],
  templateUrl: './storefront.component.html',
  styles: [
    `
      :host {
        display: block;
      }
      @keyframes marqueeScroll {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
      }
      @keyframes fadePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.35; }
      }
      @keyframes colorGlowPulse {
        0%, 100% { color: #f59e0b; text-shadow: 0 0 8px rgba(245, 158, 11, 0.4); }
        33% { color: #10b981; text-shadow: 0 0 12px rgba(16, 185, 129, 0.6); }
        66% { color: #3b82f6; text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
      }
      .announcement-marquee-track {
        display: inline-block;
        white-space: nowrap;
        animation: marqueeScroll 18s linear infinite;
        will-change: transform;
      }
      .announcement-marquee-track:hover {
        animation-play-state: paused;
      }
      .announcement-fade {
        animation: fadePulse 2.5s ease-in-out infinite;
      }
      .announcement-bounce {
        animation: colorGlowPulse 3s ease-in-out infinite;
      }
    `,
  ],
})
export class StorefrontComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  public readonly cart = inject(CartStore);
  public readonly publicSettingStore = inject(PublicSettingStore);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  public readonly dismissedAnnouncement = signal<boolean>(false);
  public readonly announcement = this.publicSettingStore.settings;

  public dismissAnnouncement() {
    this.dismissedAnnouncement.set(true);
  }

  public get announcementText(): string {
    const s = this.announcement();
    if (this.lang.currentLang() === 'en') {
      return s.announcement_text_en || s.announcement_text_vi || s.announcement_text || '';
    }
    return s.announcement_text_vi || s.announcement_text || s.announcement_text_en || '';
  }

  public get tierDiscountPercent(): number {
    if (!this.publicSettingStore.settings().membership_tiers_enabled) {
      return 0;
    }
    return this.state.authStore.currentUser()?.membership_tier?.discount_percent || 0;
  }

  public get tierName(): string {
    const tier = this.state.authStore.currentUser()?.membership_tier;
    if (!tier) return '';
    return this.lang.currentLang() === 'en' ? (tier.name_en || tier.name_vi) : tier.name_vi;
  }

  public getDiscountedPrice(originalPrice: number): number {
    const discount = this.tierDiscountPercent;
    if (discount <= 0) return originalPrice;
    return Math.max(0, originalPrice * (1 - discount / 100));
  }

  ngOnInit(): void {
    this.publicSettingStore.fetchPublicSettings().subscribe();
    this.state.authStore.checkMe(true, true).subscribe();
    this.state.depositStore.fetchRate().subscribe({ error: () => undefined });
    this.state.productStore.clearCache();
    this.loadAllGroupedProducts();
  }

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'name', label: this.lang.translate('product.name'), sortable: true },
      {
        key: 'price',
        label: this.lang.translate('storefront.unit_price'),
        sortable: true,
        align: 'right',
      },
      {
        key: 'available_count',
        label: this.lang.translate('product.available_stock'),
        sortable: true,
        align: 'center',
      },
      {
        key: 'actions',
        label: this.lang.translate('common.actions'),
        sortable: false,
        align: 'right',
      },
    ];
  });

  public searchQuery = '';

  public openCart() {
    if (!this.state.isAuthenticated()) {
      this.state.showToast(this.lang.translate('storefront.please_login_to_use_cart'), 'warning');
      return;
    }
    this.modalService
      .open(CartModalComponent, {
        title: this.lang.translate('cart.title'),
        size: 'lg',
      })
      .afterClosed$.subscribe((result) => {
        if (result === 'login') {
          this.router.navigate(['/login'], { queryParams: { returnUrl: '/storefront' } });
        } else if (result === 'success') {
          this.state.authStore.checkMe(true).subscribe();
          this.router.navigate(['/orders']);
        }
      });
  }

  private getInitialViewMode(): 'card' | 'list' {
    try {
      const saved = localStorage.getItem('storefront_view_mode');
      if (saved === 'card' || saved === 'list') {
        return saved;
      }
    } catch {
      // Ignore reading error
    }
    return 'card';
  }

  public readonly limit = 100;
  public readonly categoryPages = signal<{ [catId: string]: number }>({});
  public readonly viewMode = signal<'card' | 'list'>(this.getInitialViewMode());
  public readonly categorySort = signal<'az' | 'za' | 'default'>('az');

  public setViewMode(mode: 'card' | 'list') {
    this.viewMode.set(mode);
    try {
      localStorage.setItem('storefront_view_mode', mode);
    } catch {
      // Ignore writing error
    }
  }

  public readonly includeOutOfStock = signal<boolean>(false);
  public readonly isUpdatingOutOfStock = signal<boolean>(false);

  public toggleIncludeOutOfStock(enabled: boolean) {
    if (this.isUpdatingOutOfStock() || this.state.productStore.loading()) return;
    this.isUpdatingOutOfStock.set(true);
    this.includeOutOfStock.set(enabled);
    this.loadAllGroupedProducts();
  }

  public readonly groupedProductsState = signal<any[]>([]);

  public readonly sortOptions = computed(() => [
    { label: this.lang.translate('storefront.sort_az'), value: 'az' },
    { label: this.lang.translate('storefront.sort_za'), value: 'za' },
    { label: this.lang.translate('storefront.sort_default'), value: 'default' },
  ]);

  public readonly groupedProducts = computed(() => {
    const language = this.lang.currentLang();
    const isSearching = !!this.searchQuery.trim();

    let groups = this.groupedProductsState().map((group) => ({
      ...group,
      name: language === 'vi'
        ? (group.name_vi || group.name_en || group.name)
        : (group.name_en || group.name_vi || group.name),
      description: language === 'vi'
        ? (group.description_vi ?? group.description_en ?? group.description)
        : (group.description_en ?? group.description_vi ?? group.description),
      products: (group.products || []).map((product: any) => ({
        ...product,
        name: language === 'vi'
          ? (product.name_vi || product.name_en || product.name)
          : (product.name_en || product.name_vi || product.name),
        description: language === 'vi'
          ? (product.description_vi ?? product.description_en ?? product.description)
          : (product.description_en ?? product.description_vi ?? product.description),
      })),
    }));

    if (isSearching) {
      if (!this.includeOutOfStock()) {
        groups = groups.map((g) => {
          const inStockProds = (g.products || []).filter((p: any) => (p.available_count ?? 0) > 0);
          return {
            ...g,
            products: inStockProds,
            total: inStockProds.length < g.total ? inStockProds.length : g.total,
          };
        });
      }
      groups = groups.filter((g) => (g.total ?? 0) > 0 || (g.products && g.products.length > 0));
    }

    const sortVal = this.categorySort();

    const otherGroup = groups.find((g) => g.id === 'other');
    const actualGroups = groups.filter((g) => g.id !== 'other');

    if (sortVal === 'az') {
      actualGroups.sort((a, b) => a.name.localeCompare(b.name, 'vi', { sensitivity: 'base' }));
    } else if (sortVal === 'za') {
      actualGroups.sort((a, b) => b.name.localeCompare(a.name, 'vi', { sensitivity: 'base' }));
    }

    const sorted = [...actualGroups];
    if (otherGroup) {
      sorted.push(otherGroup);
    }

    return sorted;
  });

  public loadAllGroupedProducts() {
    this.state.productStore.loading.set(true);
    this.state.productStore
      .fetchGroupedProducts(this.limit, this.searchQuery.trim() || undefined, this.includeOutOfStock())
      .subscribe({
        next: (res: any[]) => {
          const initialPages: { [catId: string]: number } = {};
          const list = (res || []).map((group) => {
            initialPages[group.id] = 1;
            return {
              ...group,
              loading: false,
            };
          });
          this.groupedProductsState.set(list);
          this.categoryPages.set(initialPages);
          this.state.productStore.loading.set(false);
          this.isUpdatingOutOfStock.set(false);
        },
        error: () => {
          this.groupedProductsState.set([]);
          this.state.productStore.loading.set(false);
          this.isUpdatingOutOfStock.set(false);
        },
      });
  }

  public loadProductsForCategory(catId: string, page: number) {
    const searchVal = this.searchQuery.trim() || undefined;

    this.groupedProductsState.update((list) =>
      list.map((group) => (group.id === catId ? { ...group, loading: true } : group)),
    );

    const apiCategoryId = catId === 'other' ? 'other' : catId;

    this.state.productStore
      .getProductsRaw(page, this.limit, apiCategoryId, searchVal, 'active', undefined, this.includeOutOfStock())
      .subscribe({
        next: (res: any) => {
          const items = res?.data || [];
          const total = res?.meta?.total || 0;

          this.groupedProductsState.update((list) =>
            list.map((group) =>
              group.id === catId ? { ...group, products: items, total, loading: false } : group,
            ),
          );

          this.categoryPages.update((prev) => ({
            ...prev,
            [catId]: page,
          }));
        },
        error: () => {
          this.groupedProductsState.update((list) =>
            list.map((group) => (group.id === catId ? { ...group, loading: false } : group)),
          );
        },
      });
  }


  public onSearch(query: string) {
    this.searchQuery = query;
    this.loadAllGroupedProducts();
  }

  public getCategoryPage(catId: string): number {
    return this.categoryPages()[catId] || 1;
  }

  public onCategoryPageChange(catId: string, page: number) {
    this.loadProductsForCategory(catId, page);
  }

  public getTotalPages(totalItems: number): number {
    return Math.ceil(totalItems / this.limit);
  }

  public buyNow(product: any) {
    if (!this.state.isAuthenticated()) {
      this.state.showToast(this.lang.translate('storefront.please_login_to_buy'), 'warning');
      return;
    }
    if (product.available_count <= 0) {
      this.state.showToast(this.lang.translate('storefront.out_of_stock'), 'warning');
      return;
    }
    this.cart.setSingleProduct(product, 1);
    this.modalService
      .open(CartModalComponent, {
        title: this.lang.translate('cart.confirm_order_title'),
        size: 'lg',
      })
      .afterClosed$.subscribe((result) => {
        if (result === 'login') {
          this.router.navigate(['/login'], { queryParams: { returnUrl: '/storefront' } });
        } else if (result === 'success') {
          this.state.authStore.checkMe(true).subscribe();
          this.router.navigate(['/orders']);
        }
      });
  }

  public addToCart(product: any) {
    this.buyNow(product);
  }
}
