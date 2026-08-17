import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ToastService } from '@core/services/toast.service';
import { ApiService } from '@core/services/api.service';
import { firstValueFrom } from 'rxjs';

@Component({
  selector: 'app-check-uid',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './check-uid.component.html',
  styleUrls: ['./check-uid.component.scss']
})
export class CheckUidComponent {
  public readonly lang = inject(LanguageService);
  private readonly toast = inject(ToastService);
  private readonly apiService = inject(ApiService);

  public rawInput = '';
  public isChecking = signal<boolean>(false);
  public progressPercent = signal<number>(0);
  public processedCount = signal<number>(0);
  public totalCount = signal<number>(0);

  public liveCount = signal<number>(0);
  public dieCount = signal<number>(0);
  public liveUIDsText = signal<string>('');
  public dieUIDsText = signal<string>('');

  public loadSampleUIDs(): void {
    this.rawInput = [
      '4|Mark_Zuckerberg',
      '100000000000000|Sample_Live_Account',
      '9999999999999999999999|Sample_Die_Account',
      '100000000000001|Demo_UID_2'
    ].join('\n');
  }

  public clearInput(): void {
    this.rawInput = '';
    this.resetResults();
  }

  private resetResults(): void {
    this.progressPercent.set(0);
    this.processedCount.set(0);
    this.totalCount.set(0);
    this.liveCount.set(0);
    this.dieCount.set(0);
    this.liveUIDsText.set('');
    this.dieUIDsText.set('');
  }

  public async startCheck(): Promise<void> {
    if (this.isChecking()) {
      return;
    }

    const lines = this.rawInput
      .replace(/\r\n/g, '\n')
      .replace(/\r/g, '\n')
      .split('\n')
      .map(line => this.normalizeLine(line))
      .filter(line => line.length > 0);

    if (lines.length === 0) {
      this.toast.showError('toast.no_uids');
      return;
    }

    this.isChecking.set(true);
    this.resetResults();
    this.totalCount.set(lines.length);

    const batchSize = 100;
    const liveList: string[] = [];
    const dieList: string[] = [];

    let processed = 0;

    for (let i = 0; i < lines.length; i += batchSize) {
      const batch = lines.slice(i, i + batchSize);

      try {
        const response: any = await firstValueFrom(
          this.apiService.post<any>('v1/tools/check-uid', { uids: batch })
        );
        if (response?.success && response.data) {
          const details = response.data.details || [];
          for (const d of details) {
            if (d.status === 'live') {
              liveList.push(d.raw);
            } else {
              dieList.push(d.raw);
            }
          }
          this.liveCount.set(liveList.length);
          this.dieCount.set(dieList.length);
          this.liveUIDsText.set(liveList.join('\n'));
          this.dieUIDsText.set(dieList.join('\n'));

          processed += batch.length;
          this.processedCount.set(processed);
          this.progressPercent.set(Math.round((processed / lines.length) * 100));
          continue;
        }
      } catch {
        // Fallback to client-side check if backend API fails
      }

      await Promise.all(
        batch.map(async rawLine => {
          const uidClean = this.extractUid(rawLine);
          const isLive = await this.checkSingleUid(uidClean);

          if (isLive) {
            liveList.push(rawLine);
            this.liveCount.set(liveList.length);
            this.liveUIDsText.set(liveList.join('\n'));
          } else {
            dieList.push(rawLine);
            this.dieCount.set(dieList.length);
            this.dieUIDsText.set(dieList.join('\n'));
          }

          processed++;
          this.processedCount.set(processed);
          const percent = Math.round((processed / lines.length) * 100);
          this.progressPercent.set(percent);
        })
      );
    }

    this.progressPercent.set(100);
    this.isChecking.set(false);
  }

  private normalizeLine(s: string | null): string {
    if (!s) return '';
    return s
      .replace(/\uFEFF/g, '')
      .replace(/[\u200B-\u200D]/g, '')
      .replace(/\u00A0/g, ' ')
      .trim();
  }

  private extractUid(t: string): string | null {
    if (!t) return null;
    const clean = this.normalizeLine(t);

    const mCookie = clean.match(/c_user\s*=\s*(\d{4,20})/);
    if (mCookie) return mCookie[1];

    const mStart = clean.match(/^(\d{1,20})\b/);
    if (mStart) return mStart[1];

    const parts = clean.split(/[|:\s]+/);
    if (parts[0] && /^\d{1,20}$/.test(parts[0].trim())) {
      return parts[0].trim();
    }

    const mAny = clean.match(/\b(\d{4,20})\b/);
    return mAny ? mAny[1] : null;
  }

  private async checkSingleUid(uid: string | null): Promise<boolean> {
    if (!uid || !/^\d+$/.test(uid)) {
      return false;
    }

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 6000);

      const response = await fetch(`https://graph2.facebook.com/v3.3/${uid}/picture?redirect=0`, {
        signal: controller.signal,
        cache: 'no-store'
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        return false;
      }

      const text = await response.text();
      const hMatch = text.match(/"height"\s*:\s*(\d+)/);
      const wMatch = text.match(/"width"\s*:\s*(\d+)/);
      return !!(hMatch && wMatch && Number(hMatch[1]) > 0 && Number(wMatch[1]) > 0);
    } catch {
      return false;
    }
  }

  public copyLiveUIDs(): void {
    const text = this.liveUIDsText();
    if (!text) {
      return;
    }
    navigator.clipboard.writeText(text).then(() => {
      this.toast.showSuccess('toast.copy_success');
    });
  }

  public copyDieUIDs(): void {
    const text = this.dieUIDsText();
    if (!text) {
      return;
    }
    navigator.clipboard.writeText(text).then(() => {
      this.toast.showSuccess('toast.copy_success');
    });
  }

  public downloadLiveTxt(): void {
    const text = this.liveUIDsText();
    if (!text) {
      return;
    }
    this.downloadBlob(text, 'live_uids.txt');
  }

  public downloadDieTxt(): void {
    const text = this.dieUIDsText();
    if (!text) {
      return;
    }
    this.downloadBlob(text, 'die_uids.txt');
  }

  private downloadBlob(content: string, filename: string): void {
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
    this.toast.showSuccess('toast.download_success');
  }
}