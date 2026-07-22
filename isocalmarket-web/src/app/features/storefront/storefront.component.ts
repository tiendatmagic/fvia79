import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { CartStore } from './cart.store';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ModalService } from '@core/services/modal.service';
import { CartModalComponent } from './cart-modal/cart-modal.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';

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
    CurrencyPipe
  ],
  templateUrl: './storefront.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class StorefrontComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  public readonly cart = inject(CartStore);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'name', label: this.lang.translate('product.name'), sortable: true },
      { key: 'price', label: this.lang.translate('storefront.unit_price'), sortable: true, align: 'right' },
      { key: 'available_count', label: this.lang.translate('product.available_stock'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';

  public openCart() {
    if (!this.state.isAuthenticated()) {
      this.state.showToast(this.lang.translate('storefront.please_login_to_use_cart'), 'warning');
      return;
    }
    this.modalService.open(CartModalComponent, {
      title: this.lang.translate('cart.title'),
      size: 'lg'
    }).afterClosed$.subscribe((result) => {
      if (result === 'login') {
        this.router.navigate(['/login'], { queryParams: { returnUrl: '/storefront' } });
      } else if (result === 'success') {
        this.state.authStore.checkMe(true).subscribe();
        this.router.navigate(['/orders']);
      }
    });
  }
  
  public readonly limit = 8;
  public readonly categoryPages = signal<{ [catId: string]: number }>({});
  public readonly viewMode = signal<'card' | 'list'>('card');
  public readonly categorySort = signal<'az' | 'za' | 'default'>('az');

  public readonly groupedProductsState = signal<any[]>([]);

  public readonly sortOptions = computed(() => [
    { label: this.lang.translate('storefront.sort_az'), value: 'az' },
    { label: this.lang.translate('storefront.sort_za'), value: 'za' },
    { label: this.lang.translate('storefront.sort_default'), value: 'default' }
  ]);

  public readonly groupedProducts = computed(() => {
    const groups = this.groupedProductsState();
    const sortVal = this.categorySort();
    
    const otherGroup = groups.find(g => g.id === 'other');
    const actualGroups = groups.filter(g => g.id !== 'other');

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
    this.state.productStore.fetchGroupedProducts(this.limit, this.searchQuery.trim() || undefined).subscribe({
      next: (res: any[]) => {
        const initialPages: { [catId: string]: number } = {};
        const list = (res || []).map(group => {
          initialPages[group.id] = 1;
          return {
            ...group,
            loading: false
          };
        });
        this.groupedProductsState.set(list);
        this.categoryPages.set(initialPages);
        this.state.productStore.loading.set(false);
      },
      error: () => {
        this.groupedProductsState.set([]);
        this.state.productStore.loading.set(false);
      }
    });
  }

  public loadProductsForCategory(catId: string, page: number) {
    const searchVal = this.searchQuery.trim() || undefined;
    
    this.groupedProductsState.update(list =>
      list.map(group => group.id === catId ? { ...group, loading: true } : group)
    );

    const apiCategoryId = catId === 'other' ? 'other' : catId;

    this.state.productStore.getProductsRaw(
      page,
      this.limit,
      apiCategoryId,
      searchVal,
      'active'
    ).subscribe({
      next: (res: any) => {
        const items = res?.data || [];
        const total = res?.meta?.total || 0;

        this.groupedProductsState.update(list =>
          list.map(group => group.id === catId ? { ...group, products: items, total, loading: false } : group)
        );

        this.categoryPages.update(prev => ({
          ...prev,
          [catId]: page
        }));
      },
      error: () => {
        this.groupedProductsState.update(list =>
          list.map(group => group.id === catId ? { ...group, loading: false } : group)
        );
      }
    });
  }

  ngOnInit() {
    this.state.authStore.checkMe(true).subscribe();
    this.state.productStore.clearCache();
    this.loadAllGroupedProducts();
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

  public addToCart(product: any) {
    if (!this.state.isAuthenticated()) {
      this.state.showToast(this.lang.translate('storefront.please_login_to_add_to_cart'), 'warning');
      return;
    }
    if (product.available_count <= 0) {
      this.state.showToast(this.lang.translate('storefront.out_of_stock'), 'warning');
      return;
    }
    this.cart.addToCart(product, 1);
    this.state.showToast(this.lang.translate('storefront.added_to_cart'), 'success');
  }
}

