import { Component, inject, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { environment } from '@environments/environment';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-user-api',
  standalone: true,
  imports: [
    CommonModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent,
    RouterModule,
    TranslatePipe
  ],
  templateUrl: './user-api.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class UserApiComponent {
  public readonly state = inject(StateService);
  public readonly activeTab = signal<'bash' | 'cmd'>('bash');
  public readonly showApiKey = signal<boolean>(false);

  public toggleShowApiKey() {
    this.showApiKey.update(val => !val);
  }

  public readonly isAdmin = computed(() => {
    return this.state.currentUser()?.role === 'admin';
  });

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

  public readonly lang = inject(LanguageService);

  public copyToClipboard(text: string, title: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.state.showToast(this.lang.translate('toast.copied'), 'success');
      }).catch(() => {
        this.state.showToast(this.lang.translate('toast.copy_error'), 'error');
      });
    } else {
      this.state.showToast(this.lang.translate('toast.copy_not_supported'), 'warning');
    }
  }

  public copyGetProductsCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/b2b/products?api_key=${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get Products');
  }

  public copyGetProductsUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/b2b/products?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get Products');
  }

  public copyGetProductDetailCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/b2b/products/PRODUCT_ID?api_key=${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get Product Detail');
  }

  public copyGetProductDetailUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/b2b/products/PRODUCT_ID?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get Product Detail');
  }

  public copyBuyCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/b2b/buy" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d '{"product_id": "PRODUCT_ID", "quantity": 1, "coupon_code": "DISCOUNT10"}'`;
    this.copyToClipboard(text, 'mẫu CURL Buy 1 sản phẩm');
  }

  public copyBuyWindowsCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/b2b/buy" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d "{\\"product_id\\": \\"PRODUCT_ID\\", \\"quantity\\": 1, \\"coupon_code\\": \\"DISCOUNT10\\"}"`;
    this.copyToClipboard(text, 'mẫu CURL Buy 1 sản phẩm (Windows CMD)');
  }

  public copyBuyMultiCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/b2b/buy" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d '{"items": [{"product_id": "PRODUCT_ID_1", "quantity": 2}, {"product_id": "PRODUCT_ID_2", "quantity": 1}], "coupon_code": "DISCOUNT10"}'`;
    this.copyToClipboard(text, 'mẫu CURL Buy nhiều sản phẩm');
  }

  public copyBuyMultiWindowsCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X POST "${this.siteUrl()}/api/v1/b2b/buy" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d "{\\"items\\": [{\\"product_id\\": \\"PRODUCT_ID_1\\", \\"quantity\\": 2}, {\\"product_id\\": \\"PRODUCT_ID_2\\", \\"quantity\\": 1}], \\"coupon_code\\": \\"DISCOUNT10\\"}"`;
    this.copyToClipboard(text, 'mẫu CURL Buy nhiều sản phẩm (Windows CMD)');
  }

  public copyBuyUrl() {
    const text = `${this.siteUrl()}/api/v1/b2b/buy`;
    this.copyToClipboard(text, 'đường dẫn Buy Product');
  }

  public copyGetMeCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/b2b/me" -H "X-API-KEY: ${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get Me');
  }

  public copyGetMeUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/b2b/me?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get Me');
  }

  public copyGetOrderCurl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `curl -X GET "${this.siteUrl()}/api/v1/b2b/orders/ORDER_CODE" -H "X-API-KEY: ${key}"`;
    this.copyToClipboard(text, 'mẫu CURL Get Order Detail');
  }

  public copyGetOrderUrl() {
    const key = this.apiKey() || 'API_KEY_CUA_BAN';
    const text = `${this.siteUrl()}/api/v1/b2b/orders/ORDER_CODE?api_key=${key}`;
    this.copyToClipboard(text, 'đường dẫn Get Order Detail');
  }

  public regenerateKey() {
    if (this.state.authStore.authLoading()) return;
    this.state.authStore.regenerateApiKey().subscribe();
  }
}
