import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomCheckboxComponent } from '@shared/components/custom-checkbox/custom-checkbox.component';

import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

export const WELCOME_NOTICE_STORAGE_KEY = 'welcome_notice_suppressed_until';

export function shouldShowWelcomeNotice(): boolean {
  try {
    const raw = localStorage.getItem(WELCOME_NOTICE_STORAGE_KEY);
    console.log('[shouldShowWelcomeNotice] Raw value in localStorage:', raw);
    if (!raw) return true;
    const suppressedUntil = parseInt(raw, 10);
    const now = Date.now();
    const result = now > suppressedUntil;
    console.log('[shouldShowWelcomeNotice] suppressedUntil:', suppressedUntil, 'now:', now, 'should show?', result);
    return result;
  } catch (err) {
    console.error('[shouldShowWelcomeNotice] Error reading localStorage:', err);
    return true;
  }
}

@Component({
  selector: 'app-welcome-notice-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ButtonComponent, CustomCheckboxComponent, IconComponent, TranslatePipe],
  styles: [':host { display: block; }'],
  templateUrl: './welcome-notice-modal.component.html',
})
export class WelcomeNoticeModalComponent {
  private readonly modalRef = inject(ModalRef);

    public suppressFor7Days = signal<boolean>(false);

  public onClose(): void {
    this.savePreference();
    this.modalRef.close();
  }

  private savePreference(): void {
    try {
      if (this.suppressFor7Days()) {
        const sevenDaysMs = 7 * 24 * 60 * 60 * 1000;
        localStorage.setItem(WELCOME_NOTICE_STORAGE_KEY, String(Date.now() + sevenDaysMs));
      } else {
        localStorage.removeItem(WELCOME_NOTICE_STORAGE_KEY);
      }
    } catch {
    }
  }
}
