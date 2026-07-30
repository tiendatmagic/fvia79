import { Pipe, PipeTransform, inject } from '@angular/core';
import { CurrencyService } from '@core/services/currency.service';

@Pipe({
  name: 'appCurrency',
  standalone: true,
  pure: false
})
export class CurrencyPipe implements PipeTransform {
  private readonly currencyService = inject(CurrencyService);

  transform(
    value: number | string | null | undefined,
    roundToThousandsOrDecimals?: boolean | number,
    decimals?: number
  ): string {
    if (typeof roundToThousandsOrDecimals === 'number') {
      return this.currencyService.format(value, { roundToThousands: false, decimals: roundToThousandsOrDecimals });
    }
    return this.currencyService.format(value, {
      roundToThousands: !!roundToThousandsOrDecimals,
      decimals
    });
  }
}
