import { Component, OnInit, inject, signal, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';

export interface UserTelegramStatus {
  telegram_chat_id: string | null;
  telegram_notifications_enabled: boolean;
  is_connected: boolean;
  bot_username: string;
}

@Component({
  selector: 'app-profile-notifications',
  standalone: true,
  imports: [
    CommonModule,
    TranslatePipe,
    ButtonComponent,
    IconComponent,
    CustomSwitchComponent
  ],
  templateUrl: './profile-notifications.component.html',
  styleUrls: ['./profile-notifications.component.scss']
})
export class ProfileNotificationsComponent implements OnInit {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly cdr = inject(ChangeDetectorRef);
  public readonly lang = inject(LanguageService);

  public readonly loading = signal<boolean>(true);
  public readonly saving = signal<boolean>(false);
  public readonly testing = signal<boolean>(false);
  public readonly disconnecting = signal<boolean>(false);
  public readonly connecting = signal<boolean>(false);

  public notificationsEnabled = true;
  public isConnected = false;
  public botUsername = 'fvia79noti_bot';

  ngOnInit(): void {
    this.fetchStatus();
  }

  public fetchStatus(): void {
    this.loading.set(true);
    this.api.get<any>('v1/user/telegram').subscribe({
      next: (res) => {
        this.loading.set(false);
        if (res && res.data) {
          this.notificationsEnabled = !!res.data.telegram_notifications_enabled;
          this.isConnected = !!res.data.is_connected;
          this.botUsername = res.data.bot_username || 'fvia79noti_bot';
        }
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading.set(false);
        this.cdr.markForCheck();
      }
    });
  }

  public connectTelegram(): void {
    if (this.connecting()) return;
    this.connecting.set(true);
    const popup = window.open('', '_blank');
    if (popup) popup.opener = null;

    this.api.post<any>('v1/user/telegram/link-token', {}).subscribe({
      next: (res) => {
        this.connecting.set(false);
        const connectUrl = res?.data?.connect_url;
        if (connectUrl && popup) {
          popup.location.href = connectUrl;
        } else {
          popup?.close();
          this.toast.showToast(this.lang.translate('profile.telegram_update_failed'), 'error');
        }
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.connecting.set(false);
        popup?.close();
        this.toast.showToast(err.error?.message || this.lang.translate('profile.telegram_update_failed'), 'error');
        this.cdr.markForCheck();
      }
    });
  }

  public toggleNotifications(val: boolean): void {
    if (this.saving()) return;
    this.saving.set(true);
    this.notificationsEnabled = val;

    this.api.post<any>('v1/user/telegram', {
      telegram_notifications_enabled: val
    }).pipe(
      finalize(() => {
        this.saving.set(false);
        this.cdr.markForCheck();
      })
    ).subscribe({
      next: () => {
        this.toast.showToast(this.lang.translate('toast.profile_updated'), 'success');
      },
      error: () => {
        this.notificationsEnabled = !val;
      }
    });
  }

  public sendTestMessage(): void {
    if (this.testing()) return;
    this.testing.set(true);

    this.api.post<any>('v1/user/telegram/test', {}).subscribe({
      next: () => {
        this.testing.set(false);
        this.toast.showToast(this.lang.translate('profile.telegram_test_success'), 'success');
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.testing.set(false);
        this.toast.showToast(err.error?.message || this.lang.translate('profile.telegram_test_failed'), 'error');
        this.cdr.markForCheck();
      }
    });
  }

  public disconnect(): void {
    if (this.disconnecting()) return;
    this.disconnecting.set(true);

    this.api.delete<any>('v1/user/telegram').subscribe({
      next: () => {
        this.disconnecting.set(false);
        this.isConnected = false;
        this.toast.showToast(this.lang.translate('profile.telegram_disconnected_success'), 'success');
        this.cdr.markForCheck();
      },
      error: () => {
        this.disconnecting.set(false);
        this.cdr.markForCheck();
      }
    });
  }
}
