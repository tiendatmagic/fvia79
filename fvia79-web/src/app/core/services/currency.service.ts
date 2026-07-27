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
    return 'VND';
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
    return Number.isFinite(savedRate) && savedRate > 0 ? savedRate : 26500;
  }

  public format(
    amount: number | string | null | undefined,
    decimalsOrOptions?: number | { roundToThousands?: boolean; decimals?: number } | boolean,
    roundToThousandsParam?: boolean
  ): string {
    const numericAmount = Number(amount) || 0;
    const curr = this.currentCurrency();

    let roundToThousands = false;
    let decimals: number | undefined = undefined;

    if (typeof decimalsOrOptions === 'boolean') {
      roundToThousands = decimalsOrOptions;
    } else if (typeof decimalsOrOptions === 'number') {
      decimals = decimalsOrOptions;
      roundToThousands = !!roundToThousandsParam;
    } else if (typeof decimalsOrOptions === 'object' && decimalsOrOptions !== null) {
      roundToThousands = !!decimalsOrOptions.roundToThousands;
      decimals = decimalsOrOptions.decimals;
    }

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
      let vndAmount = numericAmount * this.usdToVndRate();
      if (roundToThousands && vndAmount >= 1000) {
        vndAmount = Math.round(vndAmount / 1000) * 1000;
      }
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
      }).format(vndAmount);
    }
  }

  public formatBasePrice(amount: number | string | null | undefined): string {
    return this.format(amount, { roundToThousands: true });
  }

  public formatBalance(amount: number | string | null | undefined): string {
    return this.format(amount, { roundToThousands: false, decimals: 4 });
  }
}
