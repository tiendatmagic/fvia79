import { Injectable, inject, signal } from '@angular/core';
import { Observable } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';
import { ApiService } from '@core/services/api.service';

export interface DatabaseBackupStatus {
  driver: string;
  supported: boolean;
  export_available: boolean;
  restore_available: boolean;
  max_upload_megabytes: number;
  requires_otp: boolean;
}

export interface DatabaseBackupAuthorization {
  password: string;
  otp_code?: string;
  backup_file?: File;
  confirmation?: string;
}

export interface DatabaseRestoreResponse {
  message: string;
  safety_backup: string;
}

@Injectable()
export class DatabaseBackupStore {
  private readonly api = inject(ApiService);

  public readonly loading = signal(false);
  public readonly exporting = signal(false);
  public readonly restoring = signal(false);
  public readonly status = signal<DatabaseBackupStatus | null>(null);

  public fetchStatus(): Observable<DatabaseBackupStatus> {
    this.loading.set(true);

    return this.api.get<DatabaseBackupStatus>('v1/admin/database-backup/status').pipe(
      finalize(() => this.loading.set(false))
    );
  }

  public exportBackup(payload: DatabaseBackupAuthorization): Observable<HttpResponse<Blob>> {
    this.exporting.set(true);

    return this.api.postForDownload('v1/admin/database-backup/export', {
      password: payload.password,
      otp_code: payload.otp_code || null
    }).pipe(finalize(() => this.exporting.set(false)));
  }

  public restoreBackup(payload: DatabaseBackupAuthorization): Observable<DatabaseRestoreResponse> {
    const formData = new FormData();
    formData.append('password', payload.password);
    formData.append('otp_code', payload.otp_code || '');
    formData.append('confirmation', payload.confirmation || '');
    formData.append('backup_file', payload.backup_file as File);
    this.restoring.set(true);

    return this.api.postFormData<DatabaseRestoreResponse>(
      'v1/admin/database-backup/restore',
      formData
    ).pipe(finalize(() => this.restoring.set(false)));
  }
}
