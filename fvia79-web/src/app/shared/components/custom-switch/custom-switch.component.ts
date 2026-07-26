import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-custom-switch',
  
  imports: [CommonModule, FormsModule],
  templateUrl: './custom-switch.component.html',
  
  styles: [
    `
      :host {
        display: block;
      }
    `,
  ]})
export class CustomSwitchComponent {
  @Input() checked: boolean = false;
  @Output() checkedChange = new EventEmitter<boolean>();

  @Input() label: string = '';
  @Input() description: string = '';
  @Input() disabled: boolean = false;
    @Input() type: 'compact' | 'full' = 'compact';

  public onToggle(event: Event): void {
    if (this.disabled) return;
    const isChecked = (event.target as HTMLInputElement).checked;
    this.checked = isChecked;
    this.checkedChange.emit(isChecked);
  }
}
