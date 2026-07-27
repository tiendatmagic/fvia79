import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ToastService } from '@core/services/toast.service';
import { PublicSettingStore } from '@core/services/public-setting.store';

@Component({
  selector: 'app-contact',
  standalone: true,
  imports: [
    CommonModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.scss']
})
export class ContactComponent implements OnInit {
  public readonly lang = inject(LanguageService);
  private readonly toast = inject(ToastService);
  public readonly publicSettingStore = inject(PublicSettingStore);

  ngOnInit(): void {
    this.publicSettingStore.loadSettings().subscribe();
  }

  public get telegramSupportUrl(): string {
    const username = (this.publicSettingStore.settings().telegram_support_username || 'emmy203012').trim();
    if (!username) return 'https://t.me/emmy203012';
    if (username.startsWith('http://') || username.startsWith('https://')) {
      return username;
    }
    const clean = username.replace(/^@/, '');
    return `https://t.me/${clean}`;
  }

  public get telegramSupportDisplay(): string {
    const username = (this.publicSettingStore.settings().telegram_support_username || 'emmy203012').trim();
    if (!username) return '@emmy203012';
    if (username.startsWith('http://') || username.startsWith('https://')) {
      const parts = username.split('/');
      const last = parts[parts.length - 1] || username;
      return last.startsWith('@') ? last : `@${last}`;
    }
    return username.startsWith('@') ? username : `@${username}`;
  }

  public get telegramBotUrl(): string {
    const bot = (this.publicSettingStore.settings().telegram_bot_username || '').trim();
    if (!bot) return '';
    if (bot.startsWith('http://') || bot.startsWith('https://')) {
      return bot;
    }
    const clean = bot.replace(/^@/, '');
    return `https://t.me/${clean}`;
  }

  public get telegramBotUsernameDisplay(): string {
    const bot = (this.publicSettingStore.settings().telegram_bot_username || '').trim();
    if (!bot) return '';
    if (bot.startsWith('http://') || bot.startsWith('https://')) {
      const parts = bot.split('/');
      const last = parts[parts.length - 1] || bot;
      return last.startsWith('@') ? last : `@${last}`;
    }
    return bot.startsWith('@') ? bot : `@${bot}`;
  }

  public copyLink(url: string): void {
    if (!url) return;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(() => {
        this.toast.showToast(this.lang.translate('contact.link_copied'), 'success');
      }).catch(() => {
        this.fallbackCopy(url);
      });
    } else {
      this.fallbackCopy(url);
    }
  }

  private fallbackCopy(url: string): void {
    const textArea = document.createElement('textarea');
    textArea.value = url;
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      this.toast.showToast(this.lang.translate('contact.link_copied'), 'success');
    } catch {
      this.toast.showToast(url, 'warning');
    }
    document.body.removeChild(textArea);
  }
}
