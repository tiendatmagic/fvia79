import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { CategoryFormModalComponent } from './category-form-modal/category-form-modal.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';

@Component({
  selector: 'app-admin-categories',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    ButtonComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    PaginationComponent
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
  private readonly modalService = inject(ModalService);

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly selectedSort = signal<string>('name-asc');
  public readonly selectedDateRange = signal<{ startDate: string; endDate: string }>({ startDate: '', endDate: '' });
  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  public readonly sortOptions = [
    { label: 'Tên danh mục (A-Z)', value: 'name-asc' },
    { label: 'Tên danh mục (Z-A)', value: 'name-desc' },
    { label: 'Mới nhất', value: 'date-desc' },
    { label: 'Cũ nhất', value: 'date-asc' }
  ];

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
      if (sort === 'name-asc') {
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
      title: 'Thêm Danh Mục Mới',
      size: 'md',
      data: null
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCategories();
      }
    });
  }

  public openEditModal(category: any) {
    this.modalService.open(CategoryFormModalComponent, {
      title: 'Cập Nhật Danh Mục',
      size: 'md',
      data: category
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCategories();
      }
    });
  }

  public confirmDelete(category: any) {
    this.modalService.confirm({
      title: 'Xác nhận xóa danh mục',
      description: `Bạn có chắc chắn muốn xóa danh mục '${category.name}'? Hành động này không thể hoàn tác và chỉ thực hiện được nếu danh mục không chứa sản phẩm.`,
      confirmText: 'Xóa danh mục',
      cancelText: 'Hủy',
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
}
