import { CommonModule } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LanguageService } from '@core/services/language.service';
import { ToastService } from '@core/services/toast.service';
import { SupplierSettings, SupplierStore } from './supplier.store';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-admin-suppliers',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    CustomSwitchComponent,
    CustomSelectComponent,
    IconComponent,
    TranslatePipe,
  ],
  templateUrl: './admin-suppliers.component.html',
})
export class AdminSuppliersComponent implements OnInit {
  public readonly supplierStore = inject(SupplierStore);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);

  public readonly showSupplierApiKey = signal(false);
  public readonly selectedSupplierCode = signal<string | null>(null);
  public readonly isCreatingSupplier = signal(false);
  public readonly balanceLoadFailed = signal(false);
  public readonly isDeactivateModalOpen = signal(false);
  public readonly isDeactivating = signal(false);

  public get adjustmentTypeOptions() {
    return [
      { label: this.lang.translate('supplier.fixed_vnd'), value: 'fixed' },
      { label: this.lang.translate('supplier.percent'), value: 'percent' },
    ];
  }

  public get syncIntervalOptions() {
    const minUnit = this.lang.translate('supplier.minutes');
    return [
      { label: `1 ${minUnit}`, value: 1 },
      { label: `2 ${minUnit}`, value: 2 },
      { label: `5 ${minUnit}`, value: 5 },
      { label: `10 ${minUnit}`, value: 10 },
      { label: `30 ${minUnit}`, value: 30 },
    ];
  }

  public supplierForm: Partial<SupplierSettings> = {
    api_key: '',
    enabled: true,
    products_enabled: true,
    import_out_of_stock: true,
    auto_sync: false,
    sync_interval_minutes: 30,
    price_adjustment_type: 'fixed',
    price_adjustment_value: 0,
  };

  ngOnInit(): void {
    this.supplierStore.fetchSuppliers().subscribe({
      next: (suppliers) => {
        const supplier = suppliers.find((item) => item.code === 'ads7979') || suppliers[0];
        if (supplier) this.selectSupplier(supplier);
      },
      error: () => this.toast.showToast(this.lang.translate('supplier.load_failed'), 'error'),
    });
  }

  public toggleSupplierApiKeyVisibility(): void {
    this.showSupplierApiKey.set(!this.showSupplierApiKey());
  }

  public saveSupplier(): void {
    const payload = { ...this.supplierForm };
    if (!payload.api_key?.trim()) delete payload.api_key;

    if (
      this.isCreatingSupplier() &&
      (!payload.name?.trim() || !payload.base_url?.trim() || !payload.api_key?.trim())
    ) {
      this.toast.showToast(this.lang.translate('supplier.required_fields'), 'warning');
      return;
    }

    const request = this.isCreatingSupplier()
      ? this.supplierStore.create(payload)
      : this.supplierStore.update(this.selectedSupplierCode()!, payload);

    request.subscribe({
      next: (supplier) => {
        this.selectedSupplierCode.set(supplier.code);
        this.isCreatingSupplier.set(false);
        this.supplierForm = { ...supplier, api_key: '' };
        this.loadSupplierBalance(true);
        this.toast.showToast(this.lang.translate('supplier.saved'), 'success');
      },
      error: (err) =>
        this.toast.showToast(
          err?.error?.message || this.lang.translate('supplier.save_failed'),
          'error',
        ),
    });
  }

  public syncSupplier(): void {
    const code = this.selectedSupplierCode();
    if (!code) return;

    this.supplierStore.sync(code).subscribe({
      next: (response) => {
        this.supplierForm = { ...response.supplier, api_key: '' };
        this.toast.showToast(this.lang.translate('supplier.synced', response.result), 'success');
      },
      error: (err) =>
        this.toast.showToast(
          err?.error?.message || this.lang.translate('supplier.sync_failed'),
          'error',
        ),
    });
  }

  public openDeactivateModal(): void {
    if (this.isDeactivating()) return;
    this.isDeactivateModalOpen.set(true);
  }

  public closeDeactivateModal(): void {
    if (this.isDeactivating()) return;
    this.isDeactivateModalOpen.set(false);
  }

  public confirmDeactivateOutOfStock(): void {
    const code = this.selectedSupplierCode();
    if (!code || this.isDeactivating()) return;

    this.isDeactivating.set(true);
    this.supplierStore.deactivateOutOfStock(code).subscribe({
      next: (res) => {
        this.isDeactivating.set(false);
        this.isDeactivateModalOpen.set(false);
        if (res.supplier) {
          this.supplierForm = { ...res.supplier, api_key: '' };
        }
        if (res.count > 0) {
          this.toast.showToast(
            this.lang.translate('supplier.deactivate_success', { count: res.count }),
            'success',
          );
        } else {
          this.toast.showToast(this.lang.translate('supplier.deactivate_none'), 'warning');
        }
      },
      error: (err) => {
        this.isDeactivating.set(false);
        this.toast.showToast(
          err?.error?.message || this.lang.translate('supplier.deactivate_failed'),
          'error',
        );
      },
    });
  }

  public selectSupplier(supplier: SupplierSettings): void {
    this.selectedSupplierCode.set(supplier.code);
    this.isCreatingSupplier.set(false);
    this.showSupplierApiKey.set(false);
    this.supplierForm = { ...supplier, api_key: '' };
    this.loadSupplierBalance();
  }

  public startCreatingSupplier(): void {
    this.selectedSupplierCode.set(null);
    this.isCreatingSupplier.set(true);
    this.showSupplierApiKey.set(false);
    this.balanceLoadFailed.set(false);
    this.supplierStore.clearBalance();
    this.supplierForm = {
      name: '',
      base_url: 'https://',
      api_key: '',
      enabled: true,
      products_enabled: true,
      import_out_of_stock: true,
      auto_sync: false,
      sync_interval_minutes: 30,
      price_adjustment_type: 'fixed',
      price_adjustment_value: 0,
    };
  }

  public supplierExamplePriceVnd(): number {
    const basePrice = 20000;
    const adjustment = Number(this.supplierForm.price_adjustment_value || 0);
    const result =
      this.supplierForm.price_adjustment_type === 'percent'
        ? basePrice * (1 + adjustment / 100)
        : basePrice + adjustment;
    return Math.max(0, result);
  }

  public supplierExampleUsd(): number {
    const rate = this.supplierStore.exchangeRate();
    return rate > 0 ? this.supplierExamplePriceVnd() / rate : 0;
  }

  public loadSupplierBalance(forceRefresh = false): void {
    const code = this.selectedSupplierCode();
    if (!code || this.isCreatingSupplier()) return;

    this.balanceLoadFailed.set(false);
    this.supplierStore.fetchBalance(code, forceRefresh).subscribe({
      error: () => {
        if (this.selectedSupplierCode() === code) {
          this.balanceLoadFailed.set(true);
        }
      },
    });
  }
}
