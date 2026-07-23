import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '@core/services/api.service';
import { LanguageService } from '@core/services/language.service';
import { ToastService } from '@core/services/toast.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { finalize } from 'rxjs/operators';

export interface UserSessionItem {
  id: string;
  ip_address: string;
  user_agent: string;
  device_name: string;
  last_activity_at: string | null;
  created_at: string | null;
  is_current: boolean;
}

@Component({
  selector: 'app-profile-devices',
  standalone: true,
  imports: [
    CommonModule,
    IconComponent,
    PaginationComponent,
    TranslatePipe
  ],
  templateUrl: './profile-devices.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileDevicesComponent implements OnInit {
  private readonly apiService = inject(ApiService);
  public readonly lang = inject(LanguageService);
  private readonly toast = inject(ToastService);

  public readonly sessions = signal<UserSessionItem[]>([]);
  public readonly loading = signal<boolean>(true);
  public readonly revokingId = signal<string | null>(null);

  public readonly currentPage = signal<number>(1);
  public readonly totalPages = signal<number>(1);
  public readonly totalItems = signal<number>(0);
  public readonly perPage = 10;

  ngOnInit(): void {
    this.loadSessions(1);
  }

  public loadSessions(page: number = 1): void {
    this.loading.set(true);
    this.apiService.get<any>(`/user/sessions?page=${page}&per_page=${this.perPage}`)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: (res) => {
          if (res && res.data) {
            this.sessions.set(res.data);
            if (res.meta) {
              this.currentPage.set(res.meta.current_page);
              this.totalPages.set(res.meta.last_page);
              this.totalItems.set(res.meta.total);
            }
          }
        },
        error: (err) => {
          console.error('Failed to fetch login sessions:', err);
        }
      });
  }

  public onPageChange(page: number): void {
    this.loadSessions(page);
  }

  public revokeSession(sessionId: string): void {
    if (this.revokingId()) return;

    this.revokingId.set(sessionId);
    this.apiService.delete<any>(`/user/sessions/${sessionId}`)
      .pipe(finalize(() => this.revokingId.set(null)))
      .subscribe({
        next: () => {
          this.toast.showToast(this.lang.translate('toast.session_revoked'), 'success');
          this.loadSessions(this.currentPage());
        },
        error: (err) => {
          const message = err?.error?.message || this.lang.translate('toast.session_revoke_failed');
          this.toast.showToast(message, 'error');
        }
      });
  }
}
