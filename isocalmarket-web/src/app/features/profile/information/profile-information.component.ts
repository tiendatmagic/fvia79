import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-information',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent],
  templateUrl: './profile-information.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileInformationComponent implements OnInit {
  public readonly state = inject(StateService);

  public name = '';
  public email = '';
  public phone = '';
  public isSubmitting = false;

  ngOnInit() {
    const user = this.state.authStore.currentUser();
    if (user) {
      this.name = user.name;
      this.email = user.email;
      this.phone = user.phone || '';
    }
  }

  public onSave() {
    this.isSubmitting = true;
    this.state.authStore.updateProfile(this.phone)
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe();
  }
}
