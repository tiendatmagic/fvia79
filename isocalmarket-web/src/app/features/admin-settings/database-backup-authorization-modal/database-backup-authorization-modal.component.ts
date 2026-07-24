import { CommonModule } from '@angular/common';
import { Component, Inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MODAL_DATA, ModalRef } from '@core/services/modal-ref';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { DatabaseBackupAuthorization } from '../database-backup.store';

export interface DatabaseBackupModalData {
  mode: 'export' | 'restore';
  requiresOtp: boolean;
  maxUploadMegabytes: number;
}

@Component({
  selector: 'app-database-backup-authorization-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ButtonComponent, IconComponent, TranslatePipe],
  templateUrl: './database-backup-authorization-modal.component.html',
  styleUrl: './database-backup-authorization-modal.component.css'
})
export class DatabaseBackupAuthorizationModalComponent {
  public password = '';
  public otpCode = '';
  public confirmation = '';
  public selectedFile: File | null = null;
  public fileError = '';

  constructor(
    public readonly modalRef: ModalRef<DatabaseBackupAuthorization>,
    @Inject(MODAL_DATA) public readonly data: DatabaseBackupModalData
  ) {}

  public onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] || null;
    this.fileError = '';

    if (!file) {
      this.selectedFile = null;
      return;
    }

    if (!file.name.toLowerCase().endsWith('.sql')) {
      this.selectedFile = null;
      this.fileError = 'database_backup.file_type_error';
      input.value = '';
      return;
    }

    if (file.size > this.data.maxUploadMegabytes * 1024 * 1024) {
      this.selectedFile = null;
      this.fileError = 'database_backup.file_size_error';
      input.value = '';
      return;
    }

    this.selectedFile = file;
  }

  public canSubmit(): boolean {
    if (!this.password || (this.data.requiresOtp && this.otpCode.length !== 6)) {
      return false;
    }

    return this.data.mode === 'export'
      || (!!this.selectedFile && this.confirmation === 'RESTORE');
  }

  public submit(): void {
    if (!this.canSubmit()) return;

    this.modalRef.close({
      password: this.password,
      otp_code: this.otpCode || undefined,
      backup_file: this.selectedFile || undefined,
      confirmation: this.data.mode === 'restore' ? this.confirmation : undefined
    });
  }
}
