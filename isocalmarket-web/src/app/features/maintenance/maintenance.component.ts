import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { StateService } from '@core/services/state.service';

@Component({
  selector: 'app-maintenance',
  standalone: true,
  imports: [
    CommonModule,
    LogoComponent,
    ThemeSwitcherComponent
  ],
  templateUrl: './maintenance.component.html',
  styleUrl: './maintenance.component.scss'
})
export class MaintenanceComponent {
  public readonly stateService = inject(StateService);

  public retry(): void {
    window.location.reload();
  }
}
