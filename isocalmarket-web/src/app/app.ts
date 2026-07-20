import { Component, inject, OnInit, effect, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterModule } from '@angular/router';
import { HeaderComponent } from '@shared/layout/header/header.component';
import { ToastComponent } from '@shared/components/toast/toast.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SidebarComponent } from '@shared/layout/sidebar/sidebar.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { StateService } from '@core/services/state.service';
import { MaintenanceComponent } from '@features/maintenance/maintenance.component';

@Component({
  selector: 'app-root',
  
  imports: [
    CommonModule,
    RouterOutlet,
    RouterModule,
    HeaderComponent,
    SidebarComponent,
    ToastComponent,
    IconComponent,
    LogoComponent,
    ThemeSwitcherComponent,
    MaintenanceComponent
  ],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App implements OnInit {
  public stateService = inject(StateService);
  public hasBeenOpened = signal<boolean>(false);
  public activeMenuState = signal<boolean>(false);

  constructor() {
    effect(() => {
      const open = this.stateService.showMobileMenu();
      if (open) {
        this.hasBeenOpened.set(true);
        setTimeout(() => {
          this.activeMenuState.set(true);
        }, 50);
      } else {
        this.activeMenuState.set(false);
        setTimeout(() => {
          if (!this.stateService.showMobileMenu()) {
            this.hasBeenOpened.set(false);
          }
        }, 300);
      }
    });
  }

  ngOnInit() {
    this.stateService.authStore.checkMe().subscribe();
  }
}

