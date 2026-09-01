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
  public readonly isChecking = signal<boolean>(false);
  public readonly progressPercent = signal<number>(0);
  public readonly processedCount = signal<number>(0);
  public readonly totalCount = signal<number>(0);

  public readonly liveCount = signal<number>(0);
  public readonly dieCount = signal<number>(0);
  public readonly livePercent = signal<string>('0.00%');
  public readonly diePercent = signal<string>('0.00%');
  public readonly liveUIDsText = signal<string>('');
  public readonly dieUIDsText = signal<string>('');

  private readonly CONCURRENCY = 10;
  private readonly UI_TICK_MS = 150;
  private readonly FLUSH_EVERY = 20;

  private stopToken = 0;
  private liveBuf: string[] = [];
  private dieBuf: string[] = [];
  private lastUiTick = 0;

  public loadSampleUIDs(): void {
    this.rawInput = [
      '61589802000745',
      '100089457032122',
      '1424214503',
      '100008676886775',
      '100006714784899',
      '61551782038772',
      '100010474179930',
      '100000075372394',
      '4|Mark_Zuckerberg',
      '100000000000000|Sample_Live_Account'
    ].join('\n');
  }

  public async pasteFromClipboard(): Promise<void> {
    try {
      const text = await navigator.clipboard.readText();
      if (!text) {
        this.toast.showError('toast.clipboard_empty');
        return;
      }
      this.rawInput = this.rawInput ? this.rawInput + '\n' + text : text;
      this.toast.showSuccess('toast.paste_success');
    } catch {
      this.toast.showError('toast.clipboard_error');
    }
  }

  public clearInput(): void {
    this.rawInput = '';
    this.resetResults();
  }

  public clearOutputOnly(): void {
    this.liveUIDsText.set('');
    this.dieUIDsText.set('');
    this.liveBuf = [];
    this.dieBuf = [];
    this.toast.showSuccess('toast.clear_output_success');
  }

  private resetResults(): void {
    this.progressPercent.set(0);
    this.processedCount.set(0);
    this.totalCount.set(0);
    this.liveCount.set(0);
    this.dieCount.set(0);
    this.livePercent.set('0.00%');
    this.diePercent.set('0.00%');
    this.liveUIDsText.set('');
    this.dieUIDsText.set('');
    this.liveBuf = [];
    this.dieBuf = [];
    this.lastUiTick = 0;
  }

  public stopCheck(): void {
    if (!this.isChecking()) return;
    this.stopToken++;
    this.isChecking.set(false);
    this.flushBuffers();
    this.toast.showWarning('check_uid.stopped');
  }

  public async startCheck(): Promise<void> {
    if (this.isChecking()) return;

    const lines = this.parseLines(this.rawInput);
    if (lines.length === 0) {
      this.toast.showError('toast.no_uids');
      return;
    }

    this.resetResults();
    this.totalCount.set(lines.length);
    this.isChecking.set(true);

    this.stopToken++;
    const currentToken = this.stopToken;

    const total = lines.length;
    const state = { checked: 0, live: 0, die: 0 };
    let currentIndex = 0;

    const worker = async (): Promise<void> => {
      while (true) {
        if (!this.isChecking() || currentToken !== this.stopToken) return;

        const i = currentIndex++;
        if (i >= total) return;

        const line = lines[i];
        const uid = this.extractUid(line);
        const isLive = uid ? await this.checkSingleUid(uid) : false;

        if (currentToken !== this.stopToken) return;

        state.checked++;
        if (isLive) {
          state.live++;
          this.liveBuf.push(line);
        } else {
          state.die++;
          this.dieBuf.push(line);
        }

        if (state.checked % this.FLUSH_EVERY === 0) {
          this.flushBuffers();
        }
        this.updateUI(state, total);
      }
    };

    const workers = Array.from({ length: this.CONCURRENCY }, () => worker());
    await Promise.all(workers);

    if (currentToken === this.stopToken) {
      this.flushBuffers();
      this.setFinalUI(state, total);
      this.isChecking.set(false);
      this.toast.showSuccess('check_uid.completed');
    }
  }

  private parseLines(text: string): string[] {
    if (!text) return [];
    return text
      .replace(/\r\n/g, '\n')
      .replace(/\r/g, '\n')
      .split('\n')
      .map(s => this.normalizeLine(s))
      .filter(s => s.length > 0);
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

  private async checkSingleUid(uid: string): Promise<boolean> {
    if (!uid || !/^\d+$/.test(uid)) return false;

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 6000);

      const response = await fetch(`https://graph2.facebook.com/v3.3/${uid}/picture?redirect=0`, {
        signal: controller.signal,
        cache: 'no-store'
      });

      clearTimeout(timeoutId);

      if (!response.ok) return false;

      const text = await response.text();
      const hMatch = text.match(/"height"\s*:\s*(\d+)/);
      const wMatch = text.match(/"width"\s*:\s*(\d+)/);
      return !!(hMatch && wMatch && Number(hMatch[1]) > 0 && Number(wMatch[1]) > 0);
    } catch {
      return false;
    }
  }

  private flushBuffers(): void {
    if (this.liveBuf.length > 0) {
      const current = this.liveUIDsText();
      const added = this.liveBuf.join('\n');
      this.liveUIDsText.set(current ? current + '\n' + added : added);
      this.liveBuf = [];
    }

    if (this.dieBuf.length > 0) {
      const current = this.dieUIDsText();
      const added = this.dieBuf.join('\n');
      this.dieUIDsText.set(current ? current + '\n' + added : added);
      this.dieBuf = [];
    }
  }

  private fmtPct(val: number): string {
    return (Math.round(val * 100) / 100).toFixed(2) + '%';
  }

  private updateUI(state: { checked: number; live: number; die: number }, total: number): void {
    const now = performance.now();
    if (now - this.lastUiTick < this.UI_TICK_MS) return;
    this.lastUiTick = now;

    this.processedCount.set(state.checked);
    this.liveCount.set(state.live);
    this.dieCount.set(state.die);

    const checked = state.checked || 0;
    const livePct = checked ? (state.live / checked) * 100 : 0;
    const diePct = checked ? (state.die / checked) * 100 : 0;

    this.livePercent.set(this.fmtPct(livePct));
    this.diePercent.set(this.fmtPct(diePct));

    const p = total ? Math.round((state.checked / total) * 100) : 0;
    this.progressPercent.set(p);
  }

  private setFinalUI(state: { checked: number; live: number; die: number }, total: number): void {
    this.processedCount.set(state.checked);
    this.liveCount.set(state.live);
    this.dieCount.set(state.die);

    const checked = state.checked || 0;
    const livePct = checked ? (state.live / checked) * 100 : 0;
    const diePct = checked ? (state.die / checked) * 100 : 0;

    this.livePercent.set(this.fmtPct(livePct));
    this.diePercent.set(this.fmtPct(diePct));

    const p = total ? Math.round((state.checked / total) * 100) : 0;
    this.progressPercent.set(p);
  }

  public copyLiveUIDs(): void {
    const text = this.liveUIDsText();
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      this.toast.showSuccess('toast.copy_success');
    });
  }

  public copyDieUIDs(): void {
    const text = this.dieUIDsText();
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      this.toast.showSuccess('toast.copy_success');
    });
  }

  public downloadLiveTxt(): void {
    const text = this.liveUIDsText();
    if (!text) return;
    this.downloadBlob(text, 'live_uids.txt');
  }

  public downloadDieTxt(): void {
    const text = this.dieUIDsText();
    if (!text) return;
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