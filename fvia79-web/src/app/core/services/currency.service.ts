import { Injectable, signal } from '@angular/core';

export type SupportedCurrency = 'USD' | 'VND';

@Injectable({
  providedIn: 'root'
})
export class CurrencyService {
  public readonly currentCurrency = signal<SupportedCurrency>(this.getInitialCurrency());
  public readonly usdToVndRate = signal<number>(this.getInitialUsdToVndRate());

  constructor() {}

  private getInitialCurrency(): SupportedCurrency {
    const saved = localStorage.getItem('app_currency');
    if (saved === 'VND' || saved === 'USD') {
      return saved;
    }
    return 'USD';
  }

  public setCurrency(currency: SupportedCurrency): void {
    this.currentCurrency.set(currency);
    localStorage.setItem('app_currency', currency);
  }

  public setUsdToVndRate(rate: number): void {
    if (!Number.isFinite(rate) || rate <= 0) return;
    this.usdToVndRate.set(rate);
    localStorage.setItem('usd_to_vnd_rate', String(rate));
  }

  private getInitialUsdToVndRate(): number {
    const savedRate = Number(localStorage.getItem('usd_to_vnd_rate'));
    return Number.isFinite(savedRate) && savedRate > 0 ? savedRate : 26276;
  }

  public format(amount: number | string | null | undefined, decimals?: number): string {
    const numericAmount = Number(amount) || 0;
    const curr = this.currentCurrency();

    if (curr === 'USD') {
      let minDigits = 2;
      let maxDigits = decimals !== undefined ? decimals : 2;

      if (decimals === 4) {
        minDigits = 4;
        maxDigits = 4;
      } else if (decimals === undefined) {
        const rounded2 = Math.round(numericAmount * 100) / 100;
        if (Math.abs(numericAmount - rounded2) > 0.00001 && numericAmount < 10) {
          maxDigits = 4;
        }
      }

      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: minDigits,
        maximumFractionDigits: maxDigits
      }).format(numericAmount);
    } else {
      const vndAmount = numericAmount * this.usdToVndRate();
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
      }).format(vndAmount);
    }
  }

  public formatBalance(amount: number | string | null | undefined): string {
    return this.format(amount, 4);
  }
}
