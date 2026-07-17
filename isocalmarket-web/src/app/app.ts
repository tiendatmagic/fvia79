import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterModule } from '@angular/router';
import { HeaderComponent } from '@shared/layout/header/header.component';
import { ToastComponent } from '@shared/components/toast/toast.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SidebarComponent } from '@shared/layout/sidebar/sidebar.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { StateService } from '@core/services/state.service';

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
    ThemeSwitcherComponent
  ],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App implements OnInit {
  public stateService = inject(StateService);

  ngOnInit() {
    this.stateService.authStore.checkMe().subscribe();
  }
}
