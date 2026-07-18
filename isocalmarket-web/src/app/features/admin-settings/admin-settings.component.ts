import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SystemSettingStore, SystemSettingsPayload } from './system-setting.store';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
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
    SkeletonLoaderComponent
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

  public onSubmit(): void {
    if (this.submitting()) return;

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
      },
      error: () => {
        this.toast.showToast('Có lỗi xảy ra khi cập nhật cấu hình!', 'error');
      }
    });
  }
}
