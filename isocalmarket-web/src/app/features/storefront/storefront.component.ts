import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
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

@Component({
  selector: 'app-storefront',
  standalone: true,
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
  public readonly cart = inject(CartStore);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  public readonly columns: TableColumn[] = [
    { key: 'name', label: 'Tên sản phẩm', sortable: true },
    { key: 'price', label: 'Đơn giá', sortable: true, align: 'right' },
    { key: 'available_count', label: 'Tồn kho khả dụng', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public searchQuery = '';

  public openCart() {
    this.modalService.open(CartModalComponent, {
      title: 'Giỏ hàng của bạn',
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

  public readonly sortOptions = [
    { label: 'Tên danh mục (A - Z)', value: 'az' },
    { label: 'Tên danh mục (Z - A)', value: 'za' },
    { label: 'Thứ tự mặc định', value: 'default' }
  ];

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
    if (product.available_count <= 0) {
      this.state.showToast('Sản phẩm hiện đang hết hàng!', 'warning');
      return;
    }
    this.cart.addToCart(product, 1);
    this.state.showToast(`Đã thêm '${product.name}' vào giỏ hàng!`, 'success');
  }
}
