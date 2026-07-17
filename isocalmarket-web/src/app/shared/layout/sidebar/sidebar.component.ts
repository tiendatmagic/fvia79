import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';

@Component({
  selector: 'app-sidebar',
  imports: [
    CommonModule,
    RouterModule,
    IconComponent,
    LogoComponent,
    ThemeSwitcherComponent,
  ],
  templateUrl: './sidebar.component.html',
  styles: [
    `
      :host {
        display: contents;
      }
    `,
  ],
})
export class SidebarComponent {
  public stateService = inject(StateService);
}
