import { CommonModule } from '@angular/common';
import { Component, OnInit, inject } from '@angular/core';
import { ModalService } from '@core/services/modal.service';
import { LanguageService } from '@core/services/language.service';
import { ToastService } from '@core/services/toast.service';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import {
  DatabaseBackupAuthorization,
  DatabaseBackupStore
} from '../database-backup.store';
import {
  DatabaseBackupAuthorizationModalComponent,
  DatabaseBackupModalData
} from '../database-backup-authorization-modal/database-backup-authorization-modal.component';

@Component({
  selector: 'app-database-backup-panel',
  standalone: true,
  imports: [
    CommonModule,
    BadgeComponent,
    ButtonComponent,
    CardComponent,
    IconComponent,
    SkeletonLoaderComponent,
    TranslatePipe
  ],
  templateUrl: './database-backup-panel.component.html',
  styleUrl: './database-backup-panel.component.css'
})
export class DatabaseBackupPanelComponent implements OnInit {
  public readonly store = inject(DatabaseBackupStore);
  private readonly modalService = inject(ModalService);
  private readonly toast = inject(ToastService);
  private readonly lang = inject(LanguageService);

  public ngOnInit(): void {
    if (this.store.status()) return;

    this.store.fetchStatus().subscribe({
      next: status => this.store.status.set(status),
      error: () => this.toast.showToast(
        this.lang.translate('database_backup.status_failed'),
        'error'
      )
    });
  }

  public openExportModal(): void {
    const status = this.store.status();
    if (!status?.export_available || this.store.exporting()) return;

    this.openAuthorizationModal('export', status.requires_otp, status.max_upload_megabytes)
      .afterClosed$.subscribe(payload => {
        if (payload) this.exportBackup(payload);
      });
  }

  public openRestoreModal(): void {
    const status = this.store.status();
    if (!status?.restore_available || this.store.restoring()) return;

    this.openAuthorizationModal('restore', status.requires_otp, status.max_upload_megabytes)
      .afterClosed$.subscribe(payload => {
        if (payload) this.restoreBackup(payload);
      });
  }

  private openAuthorizationModal(
    mode: 'export' | 'restore',
    requiresOtp: boolean,
    maxUploadMegabytes: number
  ) {
    const data: DatabaseBackupModalData = { mode, requiresOtp, maxUploadMegabytes };

    return this.modalService.open<
      DatabaseBackupAuthorizationModalComponent,
      DatabaseBackupModalData,
      DatabaseBackupAuthorization
    >(DatabaseBackupAuthorizationModalComponent, {
      title: this.lang.translate(
        mode === 'export' ? 'database_backup.export_title' : 'database_backup.restore_title'
      ),
      size: 'lg',
      closeOnBackdropClick: true,
      data
    });
  }

  private exportBackup(payload: DatabaseBackupAuthorization): void {
    this.store.exportBackup(payload).subscribe({
      next: response => {
        if (!response.body) return;

        const disposition = response.headers.get('content-disposition') || '';
        const encodedName = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
        const basicName = disposition.match(/filename="?([^";]+)"?/i)?.[1];
        const filename = encodedName
          ? decodeURIComponent(encodedName)
          : (basicName || `fvia79-${Date.now()}.sql`);
        const url = URL.createObjectURL(response.body);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = filename;
        anchor.click();
        URL.revokeObjectURL(url);
        this.toast.showToast(this.lang.translate('database_backup.export_success'), 'success');
      },
      error: error => void this.showExportError(error)
    });
  }

  private restoreBackup(payload: DatabaseBackupAuthorization): void {
    this.store.restoreBackup(payload).subscribe({
      next: () => {
        this.toast.showToast(
          this.lang.translate('database_backup.restore_success'),
          'success'
        );
        setTimeout(() => window.location.reload(), 1500);
      },
      error: error => {
        const rawMessage = error?.error?.message || '';
        let messageKey = 'database_backup.restore_failed';

        if (rawMessage.includes('rolled back') || rawMessage.includes('trở về trạng thái')) {
          messageKey = 'database_backup.restore_failed_rolled_back';
        } else if (rawMessage.includes('safety backup') || rawMessage.includes('sao lưu an toàn')) {
          messageKey = 'database_backup.safety_backup_failed';
        } else if (rawMessage.includes('manual recovery') || rawMessage.includes('phục hồi thủ công')) {
          messageKey = 'database_backup.restore_failed_manual';
        } else if (rawMessage.includes('busy') || rawMessage.includes('đang chạy')) {
          messageKey = 'database_backup.busy';
        } else if (rawMessage) {
          this.toast.showToast(rawMessage, 'error');
          return;
        }

        this.toast.showToast(this.lang.translate(messageKey), 'error');
      }
    });
  }

  private async showExportError(error: any): Promise<void> {
    // The global interceptor owns rate-limit notifications.
    if (error?.status === 429) return;

    let message = this.lang.translate('database_backup.export_failed');

    if (error?.error instanceof Blob) {
      try {
        const payload = JSON.parse(await error.error.text());
        const firstValidationError = Object.values(payload?.errors || {})
          .flat()
          .find(value => typeof value === 'string');
        const rawMessage = firstValidationError || payload?.message;
        if (rawMessage) {
          if (rawMessage.includes('busy') || rawMessage.includes('đang chạy')) {
            message = this.lang.translate('database_backup.busy');
          } else {
            message = rawMessage;
          }
        }
      } catch {
        // Keep the translated fallback for non-JSON download errors.
      }
    }

    this.toast.showToast(message, 'error');
  }
}
