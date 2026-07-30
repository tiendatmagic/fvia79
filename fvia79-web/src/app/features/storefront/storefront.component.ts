import {
  Component,
  OnInit,
  OnDestroy,
  inject,
  signal,
  computed,
  ChangeDetectionStrategy,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router, ActivatedRoute } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ApiService } from '@core/services/api.service';
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
import { ProductIconComponent } from '@shared/components/product-icon/product-icon.component';

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
    ProductIconComponent,
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
        0% { transform: translateX(0%); }
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
        padding-left: 100%;
        animation: marqueeScroll 40s linear infinite;
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
export class StorefrontComponent implements OnInit, OnDestroy {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  public readonly cart = inject(CartStore);
  public readonly publicSettingStore = inject(PublicSettingStore);
  private readonly api = inject(ApiService);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  private readonly modalService = inject(ModalService);

  public readonly dismissedAnnouncement = signal<boolean>(false);
  public readonly announcement = this.publicSettingStore.settings;

  public readonly recentPurchases = signal<any[]>([]);
  public readonly recentPurchasesEnabled = signal<boolean>(true);
  public readonly recentPurchasesMode = signal<'demo' | 'real'>('demo');
  public readonly recentPurchasesLoading = signal<boolean>(true);

  public readonly recentDeposits = signal<any[]>([]);
  public readonly recentDepositsEnabled = signal<boolean>(true);
  public readonly recentDepositsMode = signal<'demo' | 'real'>('demo');
  public readonly recentDepositsLoading = signal<boolean>(true);

  private demoTimer: any = null;

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
    this.state.authStore.checkMe().subscribe();
    this.state.depositStore.fetchRate().subscribe({ error: () => undefined });
    this.state.productStore.clearCache();
    this.loadAllGroupedProducts();
    this.loadRecentActivities();

    this.route.queryParams.subscribe((params) => {
      const cat = params['category'];
      this.selectedCategory.set(cat ? String(cat) : 'all');
    });
  }

  ngOnDestroy(): void {
    if (this.demoTimer) {
      clearInterval(this.demoTimer);
      this.demoTimer = null;
    }
  }

  public loadRecentActivities(): void {
    this.recentPurchasesLoading.set(true);
    this.recentDepositsLoading.set(true);

    this.state.storefrontActivityStore.fetchRecentPurchases().subscribe({
      next: (res) => {
        if (res) {
          this.recentPurchasesEnabled.set(Boolean(res.enabled));
          const mode: 'demo' | 'real' = res.mode === 'real' ? 'real' : 'demo';
          this.recentPurchasesMode.set(mode);
          if (mode === 'demo') {
            this.seedInitialPurchases();
          } else if (res.data && res.data.length > 0) {
            this.recentPurchases.set(res.data);
          } else {
            this.recentPurchases.set([]);
          }
        }
        this.recentPurchasesLoading.set(false);
      },
      error: () => {
        if (this.recentPurchasesMode() === 'demo') {
          this.seedInitialPurchases();
        }
        this.recentPurchasesLoading.set(false);
      }
    });

    this.state.storefrontActivityStore.fetchRecentDeposits().subscribe({
      next: (res) => {
        if (res) {
          this.recentDepositsEnabled.set(Boolean(res.enabled));
          const mode: 'demo' | 'real' = res.mode === 'real' ? 'real' : 'demo';
          this.recentDepositsMode.set(mode);
          if (mode === 'demo') {
            this.seedInitialDeposits();
          } else if (res.data && res.data.length > 0) {
            this.recentDeposits.set(res.data);
          } else {
            this.recentDeposits.set([]);
          }
        }
        this.recentDepositsLoading.set(false);
      },
      error: () => {
        if (this.recentDepositsMode() === 'demo') {
          this.seedInitialDeposits();
        }
        this.recentDepositsLoading.set(false);
      }
    });

    this.startDemoActivitiesTimer();
  }

  private getRandomDemoUsername(excludeList: string[] = []): string {
    const sampleUsernames = [
      'minhtran99', 'tuandat_ads', 'crypto_king', 'trieu_phu_via', 'nguyen_van_a',
      'trinhphuong', 'viamaster99', 'ads79_agent', 'bm_pro_2026', 'alex_smith',
      'johnny_crypto', 'duy_fb_ads', 'hoang_long_via', 'khoa_mmo', 'trung_clone',
      'david_trader', 'son_via_v7', 'phan_tan_dinh', 'emilio_ads', 'lisa_beauty',
      'toetkool_99', 'quoc_viet_ads', 'lam_via_co', 'phong_clone_bm', 'charlie_trx',
      'tony_bep20', 'quang_huy_mmo', 'nam_ads_fb', 'hung_bm50', 'trinh_le_98',
      'xuan_bach', 'truong_giang', 'bao_long_via', 'dang_khoa_ads', 'gaooblu88',
      'havy_mmo', 'le_van_thanh', 'bui_cong_minh', 'vo_dinh_phong'
    ];

    let candidate = '';
    let attempts = 0;
    do {
      const name = sampleUsernames[Math.floor(Math.random() * sampleUsernames.length)];
      const clean = name.trim();
      const len = clean.length;
      const prefixLen = len <= 4 ? 2 : Math.min(Math.max(2, len - 4), 6);
      candidate = clean.substring(0, prefixLen) + '****';
      attempts++;
    } while (excludeList.includes(candidate) && attempts < 20);

    return candidate;
  }

  private rescaleDemoTimestamps(list: any[], minMinutes: number, maxMinutes: number): any[] {
    if (!list || list.length === 0) return list;
    const now = Date.now();
    const count = list.length;
    return list.map((item, index) => {
      let minutesAgo: number;
      if (count === 1) {
        minutesAgo = minMinutes;
      } else {
        const step = (maxMinutes - minMinutes) / (count - 1);
        minutesAgo = Math.round((minMinutes + index * step) * 10) / 10;
      }
      return {
        ...item,
        created_at: new Date(now - minutesAgo * 60 * 1000).toISOString()
      };
    });
  }

  private seedInitialPurchases(): void {
    const used: string[] = [];
    const getUniqueName = () => {
      const name = this.getRandomDemoUsername(used);
      used.push(name);
      return name;
    };

    const initial = [
      { id: 'seed_p1', username: getUniqueName(), quantity: 2, product_name_vi: 'Via Poland Cổ', product_name_en: 'Via Poland Old', total_price: 18.18, total_usd: 18.18 },
      { id: 'seed_p2', username: getUniqueName(), quantity: 5, product_name_vi: 'Via United States Cổ', product_name_en: 'Via United States Old', total_price: 85.57, total_usd: 85.57 },
      { id: 'seed_p3', username: getUniqueName(), quantity: 4, product_name_vi: 'Via Egypt New (No2FA)', product_name_en: 'Via Egypt New (No2FA)', total_price: 11.41, total_usd: 11.41 },
      { id: 'seed_p4', username: getUniqueName(), quantity: 1, product_name_vi: 'Via Ngoại Cổ DIE ADS (No2FA)', product_name_en: 'Via Foreign Old DIE ADS (No2FA)', total_price: 1.45, total_usd: 1.45 },
      { id: 'seed_p5', username: getUniqueName(), quantity: 3, product_name_vi: 'Via Indonesia Cổ (No2FA)', product_name_en: 'Via Indonesia Old (No2FA)', total_price: 10.27, total_usd: 10.27 },
      { id: 'seed_p6', username: getUniqueName(), quantity: 2, product_name_vi: 'Via Indonesia Cổ', product_name_en: 'Via Indonesia Old', total_price: 11.41, total_usd: 11.41 },
      { id: 'seed_p7', username: getUniqueName(), quantity: 5, product_name_vi: 'Via Czech Republic Cổ', product_name_en: 'Via Czech Republic Old', total_price: 45.45, total_usd: 45.45 },
      { id: 'seed_p8', username: getUniqueName(), quantity: 2, product_name_vi: 'Via Congo New (No2FA)', product_name_en: 'Via Congo New (No2FA)', total_price: 5.70, total_usd: 5.70 }
    ];

    this.recentPurchases.set(this.rescaleDemoTimestamps(initial, 5, 15));
  }

  private seedInitialDeposits(): void {
    const used: string[] = [];
    const getUniqueName = () => {
      const name = this.getRandomDemoUsername(used);
      used.push(name);
      return name;
    };

    const initial = [
      { id: 'seed_d1', username: getUniqueName(), amount_usdt: 10, method: 'usdt_bep20' },
      { id: 'seed_d2', username: getUniqueName(), amount_usdt: 85, method: 'usdt_bep20' },
      { id: 'seed_d3', username: getUniqueName(), amount_usdt: 15, method: 'usdt_bep20' },
      { id: 'seed_d4', username: getUniqueName(), amount_usdt: 75, method: 'binance_pay' },
      { id: 'seed_d5', username: getUniqueName(), amount_usdt: 5, method: 'binance_pay' },
      { id: 'seed_d6', username: getUniqueName(), amount_usdt: 8.5, method: 'binance_pay' },
      { id: 'seed_d7', username: getUniqueName(), amount_usdt: 1.2, method: 'usdt_bep20' },
      { id: 'seed_d8', username: getUniqueName(), amount_usdt: 2.5, method: 'binance_pay' }
    ];

    this.recentDeposits.set(this.rescaleDemoTimestamps(initial, 20, 40));
  }

  private startDemoActivitiesTimer(): void {
    if (this.demoTimer) {
      clearInterval(this.demoTimer);
      this.demoTimer = null;
    }

    this.demoTimer = setInterval(() => {
      const isPurchasesDemo = this.recentPurchasesEnabled() && this.recentPurchasesMode() === 'demo';
      const isDepositsDemo = this.recentDepositsEnabled() && this.recentDepositsMode() === 'demo';

      if (!isPurchasesDemo && !isDepositsDemo) return;

      if (isPurchasesDemo && Math.random() > 0.3) {
        const groups = this.groupedProductsState();
        let availableProds: any[] = [];
        groups.forEach(g => {
          if (g.products && g.products.length > 0) {
            availableProds.push(...g.products);
          }
        });

        const randomName = this.getRandomDemoUsername();
        const qty = Math.floor(Math.random() * 5) + 1;
        const prod = availableProds.length > 0
          ? availableProds[Math.floor(Math.random() * availableProds.length)]
          : null;

        const prodNameVi = prod ? (prod.name_vi || prod.name) : 'Via Ngoại New';
        const prodNameEn = prod ? (prod.name_en || prod.name) : 'Via Foreign New';
        const rawUnitPrice = prod ? Number(prod.price) : 2.50;
        const unitPriceUsd = rawUnitPrice > 1000 ? rawUnitPrice / 26500 : rawUnitPrice;
        const totalUsd = Math.round(unitPriceUsd * qty * 100) / 100;

        const newItem = {
          id: 'live_purch_' + Date.now(),
          username: randomName,
          quantity: qty,
          product_name_vi: prodNameVi,
          product_name_en: prodNameEn,
          product_name: prodNameVi,
          total_price: totalUsd,
          total_usd: totalUsd
        };

        this.recentPurchases.update(list => this.rescaleDemoTimestamps([newItem, ...list].slice(0, 10), 5, 15));
      }

      if (isDepositsDemo && Math.random() > 0.3) {
        const randomName = this.getRandomDemoUsername();
        const demoUsdtAmounts = [0.1, 0.5, 1.2, 2.5, 5, 8.5, 10, 15, 25.5, 35, 45, 50, 65, 75, 80, 85.5, 95, 100];
        const randomUsdt = demoUsdtAmounts[Math.floor(Math.random() * demoUsdtAmounts.length)];
        const randomMethod = Math.random() < 0.8 ? 'usdt_bep20' : 'binance_pay';

        const newItem = {
          id: 'live_dep_' + Date.now(),
          username: randomName,
          amount_usdt: randomUsdt,
          amount_vnd: randomUsdt * 26500,
          method: randomMethod
        };

        this.recentDeposits.update(list => this.rescaleDemoTimestamps([newItem, ...list].slice(0, 10), 20, 40));
      }
    }, 12000);
  }

  public getProductName(item: any): string {
    if (!item) return '';
    const isEn = this.lang.currentLang() === 'en';
    const nameVi = item.product_name_vi || item.name_vi || item.product?.name_vi || item.product_name || item.name || item.product?.name || '';
    const nameEn = item.product_name_en || item.name_en || item.product?.name_en || (isEn ? '' : nameVi);
    return isEn ? (nameEn || nameVi) : (nameVi || nameEn);
  }

  public getDepositMethodName(method: string): string {
    if (!method) return 'USDT (BEP20)';
    const lower = method.toLowerCase();
    if (lower.includes('binance')) return 'BINANCE PAY';
    if (lower.includes('trc20')) return 'USDT (TRC20)';
    if (lower.includes('bep20') || lower.includes('usdt')) return 'USDT (BEP20)';
    if (lower.includes('acb') || lower.includes('bank')) return 'ACB';
    return method.toUpperCase();
  }

  public formatRelativeTime(created_at: string): string {
    if (!created_at) return '';
    const date = new Date(created_at);
    const diffMs = Math.max(0, Date.now() - date.getTime());
    const minutes = Math.floor(diffMs / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (minutes < 1) {
      return this.lang.translate('storefront.time_just_now');
    }
    if (minutes < 60) {
      return this.lang.translate('storefront.time_minutes_ago', { count: minutes });
    }
    if (hours < 24) {
      return this.lang.translate('storefront.time_hours_ago', { count: hours });
    }
    return this.lang.translate('storefront.time_days_ago', { count: days });
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
    }
    return 'card';
  }

  public readonly limit = 100;
  public readonly categoryPages = signal<{ [catId: string]: number }>({});
  public readonly viewMode = signal<'card' | 'list'>(this.getInitialViewMode());
  public readonly categorySort = signal<'az' | 'za' | 'default'>('default');

  public setViewMode(mode: 'card' | 'list') {
    this.viewMode.set(mode);
    try {
      localStorage.setItem('storefront_view_mode', mode);
    } catch {
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

  public readonly selectedCategory = signal<string>('all');

  public onCategorySelect(catId: string) {
    const targetCat = catId || 'all';
    this.selectedCategory.set(targetCat);
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { category: targetCat === 'all' ? null : targetCat },
      queryParamsHandling: 'merge',
    });
  }

  public readonly categoryOptions = computed(() => {
    const language = this.lang.currentLang();
    const allLabel = this.lang.translate('storefront.all_categories');

    const options: { id: string; name: string }[] = [
      { id: 'all', name: allLabel },
    ];

    const fetchedCats = (this.state.productStore.categories() || [])
      .filter((c: any) => c.is_active !== false && c.is_active !== 0 && c.is_active !== '0');
    if (fetchedCats && fetchedCats.length > 0) {
      for (const cat of fetchedCats) {
        const catName = language === 'vi'
          ? (cat.name_vi || cat.name_en || cat.name)
          : (cat.name_en || cat.name_vi || cat.name);
        options.push({
          id: String(cat.id),
          name: catName,
        });
      }
    } else {
      const existingGroups = this.groupedProductsState();
      for (const group of existingGroups) {
        if (group.id && group.id !== 'all') {
          options.push({
            id: String(group.id),
            name: group.name,
          });
        }
      }
    }

    return options;
  });

  public readonly groupedProductsState = signal<any[]>([]);

  public readonly sortOptions = computed(() => [
    { label: this.lang.translate('storefront.sort_default'), value: 'default' },
    { label: this.lang.translate('storefront.sort_az'), value: 'az' },
    { label: this.lang.translate('storefront.sort_za'), value: 'za' },
  ]);

  public readonly groupedProducts = computed(() => {
    const language = this.lang.currentLang();
    const isSearching = !!this.searchQuery.trim();
    const selectedCat = this.selectedCategory();

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

    if (selectedCat !== 'all') {
      groups = groups.filter((g) => String(g.id) === String(selectedCat));

      if (groups.length === 0) {
        const catInfo = this.state.productStore.categories().find((c: any) => String(c.id) === String(selectedCat));
        if (catInfo) {
          groups = [
            {
              id: catInfo.id,
              name: language === 'vi'
                ? (catInfo.name_vi || catInfo.name_en || catInfo.name)
                : (catInfo.name_en || catInfo.name_vi || catInfo.name),
              description: language === 'vi'
                ? (catInfo.description_vi ?? catInfo.description_en ?? catInfo.description)
                : (catInfo.description_en ?? catInfo.description_vi ?? catInfo.description),
              products: [],
              total: 0,
              loading: false,
            },
          ];
        }
      }
    }

    if (isSearching) {
      if (!this.includeOutOfStock() && selectedCat === 'all') {
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
