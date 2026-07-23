import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '@core/services/api.service';
import { LanguageService } from '@core/services/language.service';
import { ToastService } from '@core/services/toast.service';
import { ModalService } from '@core/services/modal.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
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
    TableComponent,
    TableCellDirective,
    TooltipDirective,
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
  private readonly modalService = inject(ModalService);

  public readonly sessions = signal<UserSessionItem[]>([]);
  public readonly loading = signal<boolean>(true);
  public readonly revokingId = signal<string | null>(null);
  public readonly revokingOthers = signal<boolean>(false);

  public readonly currentPage = signal<number>(1);
  public readonly totalPages = signal<number>(1);
  public readonly totalItems = signal<number>(0);
  public readonly perPage = 10;

  public readonly hasOtherSessions = computed(() => {
    return this.sessions().some(s => !s.is_current);
  });

  public get columns(): TableColumn[] {
    return [
      { key: 'ip_address', label: this.lang.translate('profile.ip_address'), sortable: true },
      { key: 'device_name', label: this.lang.translate('profile.browser_device'), sortable: true },
      { key: 'last_activity_at', label: this.lang.translate('profile.last_active'), sortable: true },
      { key: 'actions', label: this.lang.translate('profile.actions'), align: 'right' }
    ];
  }

  ngOnInit(): void {
    this.loadSessions(1);
  }

  public loadSessions(page: number = 1): void {
    this.loading.set(true);
    this.apiService.get<any>(`v1/user/sessions?page=${page}&per_page=${this.perPage}`)
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

  public confirmRevokeSession(session: UserSessionItem): void {
    if (this.revokingId()) return;

    this.modalService.confirm({
      title: this.lang.translate('modal.revoke_session_title'),
      description: `${this.lang.translate('modal.revoke_session_desc')} (${session.device_name} - IP: ${session.ip_address})`,
      confirmText: this.lang.translate('profile.revoke_session'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.revokeSession(session.id);
      }
    });
  }

  public revokeSession(sessionId: string): void {
    if (this.revokingId()) return;

    this.revokingId.set(sessionId);
    this.apiService.delete<any>(`v1/user/sessions/${sessionId}`)
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

  public confirmRevokeAllOthers(): void {
    if (this.revokingOthers()) return;

    this.modalService.confirm({
      title: this.lang.translate('modal.revoke_others_title'),
      description: this.lang.translate('modal.revoke_others_desc'),
      confirmText: this.lang.translate('profile.revoke_all_others'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.revokeAllOthers();
      }
    });
  }

  public revokeAllOthers(): void {
    if (this.revokingOthers()) return;

    this.revokingOthers.set(true);
    this.apiService.delete<any>('v1/user/sessions/others')
      .pipe(finalize(() => this.revokingOthers.set(false)))
      .subscribe({
        next: () => {
          this.toast.showToast(this.lang.translate('toast.all_other_sessions_revoked'), 'success');
          this.loadSessions(1);
        },
        error: (err) => {
          const message = err?.error?.message || this.lang.translate('toast.all_other_sessions_revoke_failed');
          this.toast.showToast(message, 'error');
        }
      });
  }
}
