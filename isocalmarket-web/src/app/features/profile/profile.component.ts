import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './profile.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileComponent {
  public readonly state = inject(StateService);

  /** Lấy chữ cái đầu của tên để làm avatar */
  public getUserInitial(): string {
    const name = this.state.currentUser()?.name || '';
    return name.charAt(0).toUpperCase();
  }
}
