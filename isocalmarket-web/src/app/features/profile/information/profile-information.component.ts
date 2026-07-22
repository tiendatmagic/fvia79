import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-information',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent, TranslatePipe],
  templateUrl: './profile-information.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileInformationComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public username = '';
  public uid = '';
  public name = '';
  public email = '';
  public phone = '';
  public readonly isSubmitting = signal(false);

  ngOnInit() {
    const user = this.state.authStore.currentUser();
    if (user) {
      this.username = user.username || '';
      this.uid = user.uid || '';
      this.name = user.name;
      this.email = user.email;
      this.phone = user.phone || '';
    }
  }

  public onSave() {
    if (this.isSubmitting()) return;

    if (!this.name) {
      this.state.showToast(this.lang.translate('toast.name_required'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.authStore.updateProfile(this.name, this.phone)
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe();
  }
}
