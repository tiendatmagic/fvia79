import { Injectable, signal, inject } from '@angular/core';
import { CurrencyService } from './currency.service';
import { EN_TRANSLATIONS } from '../i18n/en';
import { VI_TRANSLATIONS } from '../i18n/vi';

export type SupportedLanguage = 'en' | 'vi';

export const TRANSLATIONS: Record<SupportedLanguage, Record<string, string>> = {
  en: EN_TRANSLATIONS,
  vi: VI_TRANSLATIONS,
};

@Injectable({
  providedIn: 'root',
})
export class LanguageService {
  private readonly currencyService = inject(CurrencyService);
  public readonly currentLang = signal<SupportedLanguage>(this.getInitialLang());

  constructor() { }

  private getInitialLang(): SupportedLanguage {
    const saved = localStorage.getItem('app_lang');
    if (saved === 'vi' || saved === 'en') {
      return saved;
    }
    return 'vi';
  }

  public setLanguage(lang: SupportedLanguage): void {
    this.currentLang.set(lang);
    localStorage.setItem('app_lang', lang);
    this.currencyService.setCurrency(lang === 'en' ? 'USD' : 'VND');
  }

  public translate(key: string, params?: Record<string, any>): string {
    const lang = this.currentLang();
    const dictionary = TRANSLATIONS[lang] || TRANSLATIONS.vi;
    let text = dictionary[key] ?? TRANSLATIONS.vi[key] ?? TRANSLATIONS.en[key] ?? key;

    if (params) {
      Object.keys(params).forEach((param) => {
        text = text.replace(new RegExp(`{+\\s*${param}\\s*}+`, 'g'), params[param]);
      });
    }

    return text;
  }
}
