import { Pipe, PipeTransform, inject } from '@angular/core';
import { LanguageService } from '@core/services/language.service';

@Pipe({
  name: 'translate',
  standalone: true,
  pure: false // Re-evaluate when the active language signal changes.
})
export class TranslatePipe implements PipeTransform {
  private readonly langService = inject(LanguageService);

  transform(key: string, params?: Record<string, any>): string {
    if (!key) return '';
    return this.langService.translate(key, params);
  }
}
