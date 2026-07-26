import { Component, inject, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-theme-switcher',
  standalone: true,
  imports: [CommonModule, IconComponent, TranslatePipe],
  templateUrl: './theme-switcher.component.html',
  styleUrl: './theme-switcher.component.css'
})
export class ThemeSwitcherComponent {
  public stateService = inject(StateService);

  @Input() showLabels = true;
  @Input() showAuto = true;
}
