import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-terms',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './terms.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class TermsComponent {
  public readonly lang = inject(LanguageService);
}
