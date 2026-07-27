import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { LanguageService } from '@core/services/language.service';
import { CategoryFormModalComponent } from './category-form-modal/category-form-modal.component';
import { CategoryReorderModalComponent } from './category-reorder-modal/category-reorder-modal.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';

@Component({
  selector: 'app-admin-categories',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    PaginationComponent,
    BadgeComponent,
    CustomSwitchComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe
  ],
  templateUrl: './admin-categories.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminCategoriesComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  private readonly modalService = inject(ModalService);
  private readonly api = inject(ApiService);

  public readonly isDeactivateModalOpen = signal(false);
  public readonly isDeactivating = signal(false);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'id', label: this.lang.translate('common.id'), sortable: true },
      { key: 'sort_order', label: this.lang.translate('cat.sort_order'), sortable: true, align: 'center' },
      { key: 'name', label: this.lang.translate('category.name'), sortable: true },
      { key: 'slug', label: this.lang.translate('cat.slug'), sortable: true },
      { key: 'description', label: this.lang.translate('common.description'), sortable: true },
      { key: 'products_count', label: this.lang.translate('category.product_count'), sortable: true, align: 'center' },
      { key: 'status', label: this.lang.translate('cat.status'), sortable: true, align: 'center' },
      { key: 'created_at', label: this.lang.translate('common.created_at'), sortable: true },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly selectedSort = signal<string>('sort_order-asc');
  public readonly selectedDateRange = signal<{ startDate: string; endDate: string }>({ startDate: '', endDate: '' });
  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  public get sortOptions() {
    return [
      { label: this.lang.translate('cat.sort_order_asc'), value: 'sort_order-asc' },
      { label: this.lang.translate('category.name_az'), value: 'name-asc' },
      { label: this.lang.translate('category.name_za'), value: 'name-desc' },
      { label: this.lang.translate('common.newest'), value: 'date-desc' },
      { label: this.lang.translate('common.oldest'), value: 'date-asc' }
    ];
  }

  public readonly filteredCategories = computed(() => {
    const dateRange = this.selectedDateRange();
    const sort = this.selectedSort();
    let list = this.state.productStore.paginatedCategories();

    if (dateRange && dateRange.startDate) {
      const start = new Date(dateRange.startDate);
      start.setHours(0, 0, 0, 0);

      const end = dateRange.endDate ? new Date(dateRange.endDate) : new Date(dateRange.startDate);
      end.setHours(23, 59, 59, 999);

      list = list.filter(c => {
        if (!c.created_at) return false;
        const itemDate = new Date(c.created_at);
        return itemDate >= start && itemDate <= end;
      });
    }

    list = [...list].sort((a, b) => {
      if (sort === 'sort_order-asc') {
        const o1 = a.sort_order ?? 0;
        const o2 = b.sort_order ?? 0;
        if (o1 !== o2) return o1 - o2;
        return (a.name || '').localeCompare(b.name || '', 'vi');
      } else if (sort === 'name-asc') {
        return (a.name || '').localeCompare(b.name || '', 'vi');
      } else if (sort === 'name-desc') {
        return (b.name || '').localeCompare(a.name || '', 'vi');
      } else if (sort === 'date-desc') {
        const t1 = a.created_at ? new Date(a.created_at).getTime() : 0;
        const t2 = b.created_at ? new Date(b.created_at).getTime() : 0;
        return t2 - t1;
      } else if (sort === 'date-asc') {
        const t1 = a.created_at ? new Date(a.created_at).getTime() : 0;
        const t2 = b.created_at ? new Date(b.created_at).getTime() : 0;
        return t1 - t2;
      }
      return 0;
    });

    return list;
  });

  ngOnInit() {
    this.fetchCategories();
  }

  public fetchCategories() {
    this.state.productStore.fetchCategoriesPaginated(
      this.currentPage(),
      this.limit,
      this._searchQuery().trim() || undefined
    ).subscribe();
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.fetchCategories();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.currentPage.set(1);
    this.fetchCategories();
  }

  public openCreateModal() {
    this.modalService.open(CategoryFormModalComponent, {
      title: this.lang.translate('cat.add'),
      size: 'lg',
      data: null
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCategories();
      }
    });
  }

  public openReorderModal() {
    this.modalService.open(CategoryReorderModalComponent, {
      title: 'Sắp Xếp Vị Trí Danh Mục Trang Chủ',
      size: 'lg'
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCategories();
      }
    });
  }

  public openEditModal(category: any) {
    this.modalService.open(CategoryFormModalComponent, {
      title: this.lang.translate('cat.edit'),
      size: 'lg',
      data: category
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCategories();
      }
    });
  }

  public confirmDelete(category: any) {
    this.modalService.confirm({
      title: this.lang.translate('modal.delete_category_title'),
      description: `${this.lang.translate('modal.delete_category_desc')} (${category.name})`,
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.productStore.deleteCategory(category.id).subscribe((res) => {
          if (res !== null) {
            this.fetchCategories();
          }
        });
      }
    });
  }

  public updatingCategoryId = signal<string | null>(null);

  public toggleStatus(category: any) {
    if (!category || this.updatingCategoryId() === category.id) return;
    this.updatingCategoryId.set(category.id);

    const data = {
      name: category.name,
      slug: category.slug,
      description: category.description,
      is_active: !category.is_active
    };
    this.state.productStore.updateCategory(category.id, data).subscribe({
      next: (res) => {
        this.updatingCategoryId.set(null);
        if (res !== null) {
          this.state.showToast(this.lang.translate('toast.category_status_updated'), 'success');
          this.fetchCategories();
        }
      },
      error: () => {
        this.updatingCategoryId.set(null);
      }
    });
  }

  public openDeactivateOutOfStockModal() {
    if (this.isDeactivating()) return;
    this.isDeactivateModalOpen.set(true);
  }

  public closeDeactivateOutOfStockModal() {
    if (this.isDeactivating()) return;
    this.isDeactivateModalOpen.set(false);
  }

  public confirmDeactivateOutOfStock() {
    if (this.isDeactivating()) return;
    this.isDeactivating.set(true);

    this.api.post<{ message: string; count: number }>('v1/categories/deactivate-out-of-stock', {}).subscribe({
      next: (res) => {
        this.isDeactivating.set(false);
        this.isDeactivateModalOpen.set(false);
        if (res.count > 0) {
          const msg = this.lang.translate('cat.deactivate_success').replace('{{count}}', String(res.count));
          this.state.showToast(msg, 'success');
          this.fetchCategories();
        } else {
          this.state.showToast(this.lang.translate('cat.deactivate_none'), 'warning');
        }
      },
      error: (err) => {
        this.isDeactivating.set(false);
        this.state.showToast(
          err?.error?.message || this.lang.translate('cat.deactivate_failed'),
          'error',
        );
      },
    });
  }

  public updateCategorySortOrder(category: any, newOrder: number) {
    if (!category || this.updatingCategoryId() === category.id) return;
    const targetOrder = Math.max(0, Math.floor(newOrder));
    if (targetOrder === (category.sort_order ?? 0)) return;

    // Optimistic local update - no skeleton loading
    this.updatingCategoryId.set(category.id);
    const oldOrder = category.sort_order ?? 0;
    this.state.productStore.paginatedCategories.update(list =>
      list.map(c => c.id === category.id ? { ...c, sort_order: targetOrder } : c)
    );

    const data = {
      name: category.name,
      name_vi: category.name_vi || category.name,
      name_en: category.name_en || category.name,
      slug: category.slug,
      description: category.description,
      description_vi: category.description_vi,
      description_en: category.description_en,
      is_active: category.is_active,
      sort_order: targetOrder
    };

    // Silent API call - no loading.set(true)
    this.api.put<any>(`v1/categories/${category.id}`, data).subscribe({
      next: () => {
        this.updatingCategoryId.set(null);
        this.state.productStore.clearCache();
      },
      error: () => {
        // Rollback on failure
        this.updatingCategoryId.set(null);
        this.state.productStore.paginatedCategories.update(list =>
          list.map(c => c.id === category.id ? { ...c, sort_order: oldOrder } : c)
        );
        this.state.showToast(this.lang.translate('toast.category_update_failed'), 'error');
      }
    });
  }
}
