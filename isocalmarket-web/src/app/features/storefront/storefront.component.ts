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
        this.router.navigate(['/orders']);
      }
    });
  }
  
  public readonly limit = 12;
  public readonly categoryPages = signal<{ [catId: string]: number }>({});
  public readonly viewMode = signal<'card' | 'list'>('card');
  public readonly categorySort = signal<'az' | 'za' | 'default'>('az');

  public readonly sortOptions = [
    { label: 'Tên danh mục (A - Z)', value: 'az' },
    { label: 'Tên danh mục (Z - A)', value: 'za' },
    { label: 'Thứ tự mặc định', value: 'default' }
  ];
  public readonly filteredProducts = computed(() => {
    return this.state.productStore.products();
  });

  public readonly groupedProducts = computed(() => {
    const list = this.filteredProducts();
    const categories = this.state.productStore.categories();
    
    const groups: { [key: string]: any[] } = {};
    list.forEach(p => {
      const catId = p.category_id || 'other';
      if (!groups[catId]) {
        groups[catId] = [];
      }
      groups[catId].push(p);
    });

    const mapped = categories.map(cat => ({
      id: cat.id,
      name: cat.name,
      description: cat.description,
      products: groups[cat.id] || []
    })).filter(g => g.products.length > 0);

    const sortVal = this.categorySort();
    if (sortVal === 'az') {
      mapped.sort((a, b) => a.name.localeCompare(b.name, 'vi', { sensitivity: 'base' }));
    } else if (sortVal === 'za') {
      mapped.sort((a, b) => b.name.localeCompare(a.name, 'vi', { sensitivity: 'base' }));
    }

    return mapped;
  });

  ngOnInit() {
    this.state.productStore.fetchCategories().subscribe();
    this.state.productStore.fetchProducts(undefined, undefined, undefined, undefined, 'active').subscribe();
  }

  public onSearch(query: string) {
    this.searchQuery = query;
    this.state.productStore.fetchProducts(
      undefined,
      undefined,
      undefined,
      query.trim() || undefined,
      'active'
    ).subscribe();
  }

  public getCategoryPage(catId: string): number {
    return this.categoryPages()[catId] || 1;
  }

  public getPaginatedProductsForCategory(products: any[], catId: string): any[] {
    const page = this.getCategoryPage(catId);
    return products.slice((page - 1) * this.limit, page * this.limit);
  }

  public onCategoryPageChange(catId: string, page: number) {
    this.categoryPages.update(prev => ({
      ...prev,
      [catId]: page
    }));
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
