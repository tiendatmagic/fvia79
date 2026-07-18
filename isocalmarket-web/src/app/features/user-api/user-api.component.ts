import { Component, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { environment } from '@environments/environment';

@Component({
  selector: 'app-user-api',
  standalone: true,
  imports: [
    CommonModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent
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
        this.state.showToast(`Đã sao chép ${title} vào bộ nhớ tạm!`, 'success');
      }).catch(() => {
        this.state.showToast('Lỗi khi sao chép!', 'error');
      });
    } else {
      this.state.showToast('Trình duyệt không hỗ trợ sao chép tự động!', 'warning');
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
    const text = `curl -X POST "${this.siteUrl()}/api/v1/b2b/buy" -H "Content-Type: application/json" -H "X-API-KEY: ${key}" -d '{"product_id": "PRODUCT_ID", "quantity": 1}'`;
    this.copyToClipboard(text, 'mẫu CURL Buy');
  }

  public copyBuyUrl() {
    const text = `${this.siteUrl()}/api/v1/b2b/buy`;
    this.copyToClipboard(text, 'đường dẫn Buy Product');
  }

  public regenerateKey() {
    if (this.state.authStore.authLoading()) return;
    this.state.authStore.regenerateApiKey().subscribe();
  }
}
