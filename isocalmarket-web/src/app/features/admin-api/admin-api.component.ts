import { Component, inject, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
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
  public readonly lang = inject(LanguageService);
  
  public readonly importInventoryTab = signal<'bash' | 'cmd'>('bash');
  public readonly showApiKey = signal<boolean>(false);

  public toggleShowApiKey() {
    this.showApiKey.update(val => !val);
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

  // API 1: List Resellers
  public copyGetUsersCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/users?api_key=${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get B2B Users');
  }

  public copyGetUsersUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/users?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get B2B Users');
  }

  // API 2: List B2B wholesale orders
  public copyGetOrdersCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/orders?api_key=${key}&page=1&limit=10"`;
    this.copyToClipboard(text, 'mẫu CURL Get B2B Orders');
  }

  public copyGetOrdersUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/orders?api_key=${key}&page=1&limit=10`;
    this.copyToClipboard(text, 'đường dẫn Get B2B Orders');
  }

  public copyImportInventoryCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d '{"batch_name": "Lô hàng mới", "content": "tai_khoan_1\\ntai_khoan_2"}'`;
    this.copyToClipboard(text, 'mẫu CURL Import Inventory');
  }

  public copyImportInventoryWindowsCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d "{\\"batch_name\\": \\"Lô hàng mới\\", \\"content\\": \\"tai_khoan_1\\\\ntai_khoan_2\\"}"`;
    this.copyToClipboard(text, 'mẫu CURL Import Inventory (Windows CMD)');
  }

  public copyImportInventoryFileCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory" -H "X-API-KEY: ${key}" -F "batch_name=Lô hàng mới" -F "file=@inventory.txt"`;
    this.copyToClipboard(text, 'mẫu CURL Import bằng file');
  }

  public copyImportInventoryUrl() {
    const text = `${this.siteUrl()}/api/v1/admin/b2b/products/PRODUCT_UUID/inventory`;
    this.copyToClipboard(text, 'đường dẫn Import Inventory');
  }

  // API 4: Get Stats
  public copyGetStatsCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/admin/b2b/stats?api_key=${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get B2B Stats');
  }

  public copyGetStatsUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/admin/b2b/stats?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get B2B Stats');
  }
}
