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
    return 'USD'; // Mặc định là USD ($)
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

  public format(amount: number | string | null | undefined): string {
    const numericAmount = Number(amount) || 0;
    const curr = this.currentCurrency();

    if (curr === 'USD') {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(numericAmount);
    } else {
      // VND
      const vndAmount = numericAmount * this.usdToVndRate();
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
      }).format(vndAmount);
    }
  }
}
