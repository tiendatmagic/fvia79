import { Pipe, PipeTransform, inject } from '@angular/core';
import { LanguageService } from '@core/services/language.service';

@Pipe({
  name: 'translate',
  standalone: true,
  pure: false // impure pipe để lắng nghe signal currentLang tự động cập nhật UI khi đổi ngôn ngữ
})
export class TranslatePipe implements PipeTransform {
  private readonly langService = inject(LanguageService);

  transform(key: string, params?: Record<string, any>): string {
    if (!key) return '';
    return this.langService.translate(key, params);
  }
}
