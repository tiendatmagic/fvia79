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
    TooltipDirective
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

  public readonly filteredCategories = computed(() => {
    const q = this._searchQuery().trim().toLowerCase();
    const list = this.state.productStore.categories();

    if (!q) return list;

    return list.filter(c =>
      c.name?.toLowerCase().includes(q) ||
      c.slug?.toLowerCase().includes(q) ||
      c.description?.toLowerCase().includes(q)
    );
  });

  ngOnInit() {
    this.fetchCategories();
  }

  public fetchCategories() {
    this.state.productStore.fetchCategories().subscribe();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
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
