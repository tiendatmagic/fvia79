import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { TabGroupComponent, TabOption } from '@shared/components/tab-group/tab-group.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';

@Component({
  selector: 'app-tx-speed-selector',
  standalone: true,
  imports: [CommonModule, FormsModule, TabGroupComponent, TranslatePipe],
  templateUrl: './tx-speed-selector.component.html',
  styleUrl: './tx-speed-selector.component.css'
})
export class TxSpeedSelectorComponent {
  public stateService = inject(StateService);
  private readonly languageService = inject(LanguageService);

  public get speedOptions(): TabOption[] {
    return [
      { value: 'default', label: this.languageService.translate('tx.default') },
      { value: 'fast', label: this.languageService.translate('tx.fast') },
      { value: 'custom', label: this.languageService.translate('tx.custom') }
    ];
  }
}
