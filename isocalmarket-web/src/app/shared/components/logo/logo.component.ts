import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-logo',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div
      [ngClass]="{
        'border border-slate-300/70 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 shadow-2xs hover:border-primary dark:hover:border-primary hover:shadow-xs hover:shadow-primary/10': showBorder
      }"
      class="relative w-full h-full flex items-center justify-center p-1 rounded-xl transition-all duration-300 group overflow-hidden"
    >
      <img
        src="assets/logo-dark.png"
        alt="Isocalmarket Logo"
        class="w-full h-full object-contain relative z-10 transition-transform duration-300 group-hover:scale-105 dark:hidden"
      />
      <img
        src="assets/logo-light.png"
        alt="Isocalmarket Logo"
        class="w-full h-full object-contain relative z-10 transition-transform duration-300 group-hover:scale-105 hidden dark:block"
      />
    </div>
  `,
  styles: [
    `
      :host {
        display: block;
      }
    `
  ]
})
export class LogoComponent {
  @Input() showBorder: boolean = true;
}
