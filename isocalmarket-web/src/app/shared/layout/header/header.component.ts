import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    CommonModule,
    IconComponent,
    ButtonComponent,
    RouterModule,
    LogoComponent,
    ThemeSwitcherComponent
  ],
  templateUrl: './header.component.html',
  styles: [
    `
      :host {
        display: contents;
      }
    `,
  ]
})
export class HeaderComponent {
  public stateService = inject(StateService);

  public openMobileMenu() {
    this.stateService.showMobileMenu.set(true);
  }
}
