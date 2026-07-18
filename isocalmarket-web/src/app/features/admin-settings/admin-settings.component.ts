import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SystemSettingStore, SystemSettingsPayload } from './system-setting.store';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ToastService } from '@core/services/toast.service';

@Component({
  selector: 'app-admin-settings',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    SkeletonLoaderComponent,
    IconComponent
  ],
  templateUrl: './admin-settings.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminSettingsComponent implements OnInit {
  private readonly store = inject(SystemSettingStore);
  private readonly toast = inject(ToastService);

  public readonly loading = this.store.loading;
  public readonly submitting = this.store.submitting;
  public readonly isLocked = signal<boolean>(true);
  public readonly showApiKey = signal<boolean>(false);

  public form: SystemSettingsPayload = {
    admin_wallet_address: '',
    scanner_api_key: ''
  };

  ngOnInit(): void {
    this.store.fetchSettings().subscribe({
      next: (settings) => {
        if (settings) {
          this.form = { ...settings };
        }
      },
      error: () => {
        this.toast.showToast('Không thể tải cấu hình hệ thống!', 'error');
      }
    });
  }

  public toggleLock(): void {
    this.isLocked.set(!this.isLocked());
  }

  public toggleApiKeyVisibility(): void {
    this.showApiKey.set(!this.showApiKey());
  }

  public onSubmit(): void {
    if (this.submitting() || this.isLocked()) return;

    if (!this.form.admin_wallet_address.trim()) {
      this.toast.showToast('Địa chỉ ví Admin không được để trống!', 'warning');
      return;
    }
    if (!this.form.scanner_api_key.trim()) {
      this.toast.showToast('API Key Scanner không được để trống!', 'warning');
      return;
    }

    this.store.updateSettings(this.form).subscribe({
      next: () => {
        this.toast.showToast('Cập nhật cấu hình hệ thống thành công!', 'success');
        this.isLocked.set(true);
      },
      error: () => {
        this.toast.showToast('Có lỗi xảy ra khi cập nhật cấu hình!', 'error');
      }
    });
  }
}
