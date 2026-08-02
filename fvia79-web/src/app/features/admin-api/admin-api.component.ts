import { Component, inject, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { ModalService } from '@core/services/modal.service';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { environment } from '@environments/environment';
import { RouterModule } from '@angular/router';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-admin-api',
  standalone: true,
  imports: [
    CommonModule,
    PageHeaderComponent,
    CardComponent,
    IconComponent,
    ButtonComponent,
    RouterModule,
    TranslatePipe
  ],
  templateUrl: './admin-api.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminApiComponent {
  public readonly state = inject(StateService);
  public readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly importInventoryTab = signal<'bash' | 'cmd'>('bash');
  public readonly showApiKey = signal<boolean>(false);

  public toggleShowApiKey() {
    this.showApiKey.update(val => !val);
  }

  public regenerateKey() {
    if (this.state.authStore.authLoading()) return;
    this.state.authStore.regenerateApiKey().subscribe();
  }

  public readonly siteUrl = computed(() => {
    const baseUrl = environment.apiBaseUrl;
    if (baseUrl.endsWith('/api')) {
      return baseUrl.substring(0, baseUrl.length - 4);
    }
    return baseUrl;
  });

  public readonly apiKey = computed(() => {
    return this.state.currentUser()?.api_key || null;
  });

  public copyToClipboard(text: string, title: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        const successMsg = `${this.lang.translate('toast.copied')} (${title})`;
        this.state.showToast(successMsg, 'success');
      }).catch(() => {
        this.state.showToast(this.lang.translate('toast.copy_error'), 'error');
      });
    } else {
      this.state.showToast(this.lang.translate('toast.copy_not_supported'), 'warning');
    }
  }

  public copyGetUsersCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/users" -H "X-API-KEY: ${key}"`;
    this.copyToClipboard(text, this.lang.translate('api.copy_users_curl'));
  }

  public copyGetUsersUrl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/users?api_key=${key}`;
    this.copyToClipboard(text, this.lang.translate('api.copy_users_url'));
  }

  public copyGetOrdersCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/orders?page=1&limit=10" -H "X-API-KEY: ${key}"`;
    this.copyToClipboard(text, this.lang.translate('api.copy_orders_curl'));
  }

  public copyGetOrdersUrl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/orders?api_key=${key}&page=1&limit=10`;
    this.copyToClipboard(text, this.lang.translate('api.copy_orders_url'));
  }

  public copyImportInventoryCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d '{"batch_name": "New batch", "content": "account_1\\naccount_2"}'`;
    this.copyToClipboard(text, this.lang.translate('api.copy_inventory_curl'));
  }

  public copyImportInventoryWindowsCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d "{\\"batch_name\\": \\"New batch\\", \\"content\\": \\"account_1\\\\naccount_2\\"}"`;
    this.copyToClipboard(text, this.lang.translate('api.copy_inventory_cmd'));
  }

  public copyImportInventoryFileCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "X-API-KEY: ${key}" -F "batch_name=New batch" -F "file=@inventory.txt"`;
    this.copyToClipboard(text, this.lang.translate('api.copy_inventory_file'));
  }

  public copyImportInventoryUrl() {
    const text = `${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory`;
    this.copyToClipboard(text, this.lang.translate('api.copy_inventory_url'));
  }

  public copyGetStatsCurl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/stats" -H "X-API-KEY: ${key}"`;
    this.copyToClipboard(text, this.lang.translate('api.copy_stats_curl'));
  }

  public copyGetStatsUrl() {
    const key = this.apiKey() || 'YOUR_API_KEY';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/stats?api_key=${key}`;
    this.copyToClipboard(text, this.lang.translate('api.copy_stats_url'));
  }
}
