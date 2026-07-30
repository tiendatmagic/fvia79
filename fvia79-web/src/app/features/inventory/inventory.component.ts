import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ModalService } from '@core/services/modal.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { ImportInventoryModalComponent } from './import-inventory-modal/import-inventory-modal.component';
import { EditInventoryModalComponent } from './edit-inventory-modal/edit-inventory-modal.component';
import { EditResourceItemModalComponent } from './edit-resource-item-modal/edit-resource-item-modal.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ActivatedRoute, Router } from '@angular/router';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-inventory',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    PaginationComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe
  ],
  templateUrl: './inventory.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class InventoryComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  public readonly lang = inject(LanguageService);

  public activeTab = signal<'batches' | 'resources'>('batches');
  public visibleSecrets = signal<Set<string>>(new Set());

  public readonly resourceColumns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'id', label: this.lang.translate('common.id'), sortable: true },
      { key: 'product_name', label: this.lang.translate('modal.product_name'), sortable: true },
      { key: 'batch_name', label: this.lang.translate('inv.batch_name'), sortable: true },
      {
        key: 'secret_data',
        label: this.lang.translate('inventory.resource_data'),
        sortable: false,
      },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      {
        key: 'created_at',
        label: this.lang.translate('common.created_at'),
        sortable: true,
        align: 'center',
      },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public readonly batchColumns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'id', label: this.lang.translate('common.id'), sortable: true },
      { key: 'product_id', label: this.lang.translate('modal.product_name'), sortable: true },
      { key: 'batch_name', label: this.lang.translate('inv.batch_name'), sortable: true },
      { key: 'cost_price', label: this.lang.translate('inv.cost_price'), sortable: true, align: 'right' },
      { key: 'available_count', label: this.lang.translate('inv.in_stock'), sortable: true, align: 'center' },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public selectedProductFilter = '';
  public selectedStatusFilter = '';
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly currentResourcePage = signal<number>(1);
  public readonly currentBatchPage = signal<number>(1);
  public readonly limit = 10;
  public readonly loadingItemId = signal<string | null>(null);

  public get resourceStatusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('inventory.resource_available'), value: 'available' },
      { label: this.lang.translate('inventory.resource_sold'), value: 'sold' },
    ];
  }

  public get batchStatusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('common.active'), value: 'active' },
      { label: this.lang.translate('common.completed'), value: 'completed' },
      { label: this.lang.translate('common.inactive'), value: 'inactive' }
    ];
  }

  public get productFilterOptions() {
    const options = this.state.productStore.products().map(p => ({
      label: p.name,
      value: String(p.id)
    }));
    return [{ label: this.lang.translate('common.all_products'), value: '' }, ...options];
  }

  public get productOptions() {
    const options = this.state.productStore.products().map(p => ({
      label: p.name,
      value: String(p.id)
    }));
    return [{ label: this.lang.translate('modal.select_product'), value: '' }, ...options];
  }

  ngOnInit() {
    const queryParams = this.route.snapshot.queryParams;
    const tabParam = queryParams['tab'];
    if (tabParam === 'resources') {
      this.activeTab.set('resources');
    } else {
      this.activeTab.set('batches');
    }

    const productId = queryParams['product_id'] || queryParams['productId'];

    this.fetchData(1);

    this.state.productStore.fetchProducts().subscribe({
      next: () => {
        if (productId) {
          this.openImportDrawer(productId);
        }
      }
    });

    this.route.queryParams.subscribe(params => {
      const currentTab = params['tab'] === 'resources' ? 'resources' : 'batches';
      if (currentTab !== this.activeTab()) {
        this.activeTab.set(currentTab);
        this.fetchData(1);
      }
    });
  }

  public setTab(tab: 'batches' | 'resources') {
    this.activeTab.set(tab);
    this.selectedStatusFilter = '';
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { tab: tab },
      queryParamsHandling: 'merge'
    });
    this.fetchData(1);
  }

  public fetchData(page: number = 1) {
    if (this.activeTab() === 'resources') {
      this.currentResourcePage.set(page);
      this.state.inventoryStore.fetchResourceItems({
        product_id: this.selectedProductFilter || undefined,
        status: this.selectedStatusFilter || undefined,
        search: this._searchQuery().trim() || undefined
      }, page, this.limit).subscribe();
    } else {
      this.currentBatchPage.set(page);
      this.state.inventoryStore.fetchItems({
        product_id: this.selectedProductFilter || undefined,
        status: this.selectedStatusFilter || undefined,
        search: this._searchQuery().trim() || undefined
      }, page, this.limit).subscribe();
    }
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchData(1);
  }

  public fetchPage(page: number) {
    this.fetchData(page);
  }

  public toggleSecretVisibility(id: string) {
    const current = new Set(this.visibleSecrets());
    if (current.has(id)) {
      current.delete(id);
    } else {
      current.add(id);
    }
    this.visibleSecrets.set(current);
  }

  public getMaskedSecret(secret: string, isVisible: boolean): string {
    if (isVisible || !secret) return secret;
    const parts = secret.split('|');
    if (parts.length > 1) {
      return parts[0] + '|' + '••••••••';
    }
    return secret.substring(0, 4) + '••••••••';
  }

  public getProductName(productId: string): string {
    const product = this.state.productStore.products().find(p => p.id === productId);
    return product ? product.name : this.lang.translate('inventory.product_number', { number: productId });
  }

  public openImportDrawer(defaultProductId?: string) {
    const targetId = defaultProductId || this.selectedProductFilter || '';
    this.modalService.open(ImportInventoryModalComponent, {
      title: this.lang.translate('modal.import_stock_title'),
      size: '2xl',
      data: {
        selectedProductId: targetId,
        productId: targetId,
        productOptions: this.productOptions
      }
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.state.productStore.fetchProducts().subscribe();
        this.fetchData(1);
      }
    });
  }

  public openEditResourceModal(item: any) {
    this.modalService.open(EditResourceItemModalComponent, {
      title: this.lang.translate('inventory.edit_resource_title'),
      size: 'lg',
      data: { item }
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchData(this.currentResourcePage());
      }
    });
  }

  public confirmDeleteResourceItem(item: any) {
    const rawSecret = item?.secret_data || '';
    const uid = rawSecret.split('|')[0]?.trim() || rawSecret;
    const resourceDisplay = uid.length > 60 ? uid.substring(0, 60) + '...' : uid;

    this.modalService.confirm({
      title: this.lang.translate('inventory.delete_resource_title'),
      description: this.lang.translate('inventory.delete_resource_description', {
        resource: resourceDisplay,
      }),
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.inventoryStore.deleteResourceItem(item.id).subscribe((res) => {
          if (res !== null) {
            this.fetchData(this.currentResourcePage());
          }
        });
      }
    });
  }

  public openEditBatchModal(item: any) {
    if (this.loadingItemId()) return;
    this.loadingItemId.set(item.id);

    this.state.inventoryStore.fetchItemDetail(item.id).subscribe({
      next: (freshItem) => {
        this.loadingItemId.set(null);
        if (!freshItem) return;

        const cleanProductOptions = this.state.productStore.products().map(p => ({
          label: p.name,
          value: String(p.id)
        }));

        this.modalService.open(EditInventoryModalComponent, {
          title: this.lang.translate('modal.edit_stock_title'),
          size: '2xl',
          data: {
            item: freshItem,
            productOptions: cleanProductOptions
          }
        }).afterClosed$.subscribe((success) => {
          if (success) {
            this.state.productStore.fetchProducts().subscribe();
            this.fetchData(this.currentBatchPage());
          }
        });
      },
      error: () => {
        this.loadingItemId.set(null);
      }
    });
  }

  public confirmDeleteBatch(item: any) {
    this.modalService.confirm({
      title: this.lang.translate('modal.delete_inventory_title'),
      description: this.lang.translate('modal.delete_inventory_desc'),
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.inventoryStore.deleteItem(item.id, this.selectedProductFilter).subscribe((res) => {
          if (res !== null) {
            this.state.productStore.fetchProducts().subscribe();
            this.fetchData(this.currentBatchPage());
          }
        });
      }
    });
  }

  public copyToClipboard(text: string, messageKey = 'toast.copied') {
    const successMsg = this.lang.translate(messageKey);
    navigator.clipboard.writeText(text).then(
      () => this.state.showToast(successMsg, 'success'),
      () => this.state.showToast(this.lang.translate('toast.copy_error'), 'error')
    );
  }
}
