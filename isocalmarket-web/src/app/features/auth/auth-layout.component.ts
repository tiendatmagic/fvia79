import { Component, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [RouterModule, LogoComponent, ThemeSwitcherComponent, TranslatePipe],
  templateUrl: './auth-layout.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AuthLayoutComponent {
  public readonly lang = inject(LanguageService);
}
