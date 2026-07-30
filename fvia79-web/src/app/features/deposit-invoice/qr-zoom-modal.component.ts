import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MODAL_DATA, ModalRef } from '@core/services/modal-ref';
import { IconComponent } from '@shared/components/icon/icon.component';
import { QrCodeModule } from 'ng-qrcode';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

export interface QrZoomModalData {
  paymentMethod?: string;
  binancePayRawLink?: string;
  adminWalletAddress?: string;
  imageLoadError?: boolean;
}

@Component({
  selector: 'app-qr-zoom-modal',
  standalone: true,
  imports: [CommonModule, QrCodeModule, TranslatePipe],
  template: `
    <div class="flex flex-col items-center gap-4 text-center py-2">
      <p class="text-xs text-slate-500 dark:text-slate-400">
        {{ (data.paymentMethod === 'binance' ? 'invoice.qr_zoom_binance_desc' : 'invoice.qr_zoom_bep20_desc') | translate }}
      </p>

      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200 shadow-inner flex items-center justify-center my-1 w-full">
        @if (data.paymentMethod === 'binance') {
          @if (data.binancePayRawLink && !data.imageLoadError) {
            <img
              [src]="data.binancePayRawLink"
              [alt]="'invoice.binance_qr_zoom_alt' | translate"
              class="w-[340px] h-[340px] sm:w-[420px] sm:h-[420px] object-contain rounded-xl"
            />
          }
        } @else {
          <qr-code [value]="data.adminWalletAddress || ''" [size]="380" />
        }
      </div>

      <button
        type="button"
        class="w-full py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
        (click)="close()"
      >
        {{ 'invoice.qr_zoom_close' | translate }}
      </button>
    </div>
  `
})
export class QrZoomModalComponent {
  private readonly modalRef = inject(ModalRef);
  public readonly data = inject<QrZoomModalData>(MODAL_DATA);

  public close(): void {
    this.modalRef.close();
  }
}
