import { CommonModule } from '@angular/common';
import { Component, OnInit, inject } from '@angular/core';
import { ModalService } from '@core/services/modal.service';
import { LanguageService } from '@core/services/language.service';
import { ToastService } from '@core/services/toast.service';
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
    ButtonComponent,
    CardComponent,
    IconComponent,
    SkeletonLoaderComponent,
    TranslatePipe
  ],
  providers: [DatabaseBackupStore],
  templateUrl: './database-backup-panel.component.html',
  styleUrl: './database-backup-panel.component.css'
})
export class DatabaseBackupPanelComponent implements OnInit {
  public readonly store = inject(DatabaseBackupStore);
  private readonly modalService = inject(ModalService);
  private readonly toast = inject(ToastService);
  private readonly lang = inject(LanguageService);

  public ngOnInit(): void {
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
      closeOnBackdropClick: mode === 'export',
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
          : (basicName || `isocalmarket-${Date.now()}.sql`);
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
      next: response => {
        this.toast.showToast(
          response.message || this.lang.translate('database_backup.restore_success'),
          'success'
        );
        setTimeout(() => window.location.reload(), 1500);
      },
      error: error => this.toast.showToast(
        error?.error?.message || this.lang.translate('database_backup.restore_failed'),
        'error'
      )
    });
  }

  private async showExportError(error: any): Promise<void> {
    let message = this.lang.translate('database_backup.export_failed');

    if (error?.error instanceof Blob) {
      try {
        const payload = JSON.parse(await error.error.text());
        const firstValidationError = Object.values(payload?.errors || {})
          .flat()
          .find(value => typeof value === 'string');
        message = firstValidationError || payload?.message || message;
      } catch {
        // Keep the translated fallback for non-JSON download errors.
      }
    }

    this.toast.showToast(message, 'error');
  }
}
