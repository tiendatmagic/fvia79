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
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0);

    if (lines.length === 0) {
      this.toast.showError('toast.no_uids');
      return;
    }

    this.isChecking.set(true);
    this.resetResults();
    this.totalCount.set(lines.length);

    const batchSize = 30; // 30 concurrent checks per batch
    const liveList: string[] = [];
    const dieList: string[] = [];

    let processed = 0;

    for (let i = 0; i < lines.length; i += batchSize) {
      const batch = lines.slice(i, i + batchSize);

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

  private extractUid(rawLine: string): string {
    const parts = rawLine.split(/[|:\s]+/);
    return parts[0] ? parts[0].trim() : rawLine.trim();
  }

  private async checkSingleUid(uid: string): Promise<boolean> {
    if (!uid) {
      return false;
    }

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 6000);

      const response = await fetch(`https://graph.facebook.com/${uid}/picture?redirect=false`, {
        signal: controller.signal
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        return false;
      }

      const data = await response.json();
      return !!(data?.data && data.data.height > 0);
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
