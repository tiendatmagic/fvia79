import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomCheckboxComponent } from '@shared/components/custom-checkbox/custom-checkbox.component';

/** Key lưu timestamp "không hỏi lại" vào localStorage */
export const WELCOME_NOTICE_STORAGE_KEY = 'welcome_notice_suppressed_until';

/** Kiểm tra xem có nên hiển thị modal hay không */
export function shouldShowWelcomeNotice(): boolean {
  try {
    const raw = localStorage.getItem(WELCOME_NOTICE_STORAGE_KEY);
    if (!raw) return true;
    const suppressedUntil = parseInt(raw, 10);
    return Date.now() > suppressedUntil;
  } catch {
    return true;
  }
}

@Component({
  selector: 'app-welcome-notice-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ButtonComponent, CustomCheckboxComponent],
  styles: [':host { display: block; }'],
  templateUrl: './welcome-notice-modal.component.html',
})
export class WelcomeNoticeModalComponent {
  private readonly modalRef = inject(ModalRef);

  /** Mặc định tích "Không hỏi lại trong 7 ngày" */
  public suppressFor7Days = signal<boolean>(true);

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
        // Xóa setting cũ để lần sau vẫn hiện
        localStorage.removeItem(WELCOME_NOTICE_STORAGE_KEY);
      }
    } catch {
      // Ignore nếu localStorage không khả dụng
    }
  }
}
