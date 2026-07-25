import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { PublicSettingStore } from '@core/services/public-setting.store';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    IconComponent,
    LogoComponent,
    ThemeSwitcherComponent,
    TranslatePipe
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
  public publicSettingStore = inject(PublicSettingStore);

  constructor() {
    this.publicSettingStore.fetchPublicSettings().subscribe();
  }
}
