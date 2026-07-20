import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ModalService } from '@core/services/modal.service';
import { Enable2faModalComponent } from './enable-2fa-modal/enable-2fa-modal.component';
import { Disable2faModalComponent } from './disable-2fa-modal/disable-2fa-modal.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-security',
  standalone: true,
  imports: [CommonModule, ButtonComponent, BadgeComponent, IconComponent],
  templateUrl: './profile-security.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileSecurityComponent {
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);

  public readonly isSubmitting = signal(false);

  public startEnable2Fa() {
    if (this.isSubmitting()) return;
    this.isSubmitting.set(true);
    this.state.authStore.enable2Fa()
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe((res) => {
        if (res) {
          this.modalService.open(Enable2faModalComponent, {
            title: 'Kích hoạt bảo mật 2FA',
            size: 'lg',
            data: { secret: res.secret, qr_code_url: res.qr_code_url }
          });
        }
      });
  }

  public startDisable2Fa() {
    this.modalService.open(Disable2faModalComponent, {
      title: 'Hủy kích hoạt 2FA',
      size: 'md'
    });
  }
}
