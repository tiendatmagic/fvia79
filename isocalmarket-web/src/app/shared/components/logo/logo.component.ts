import { Component } from '@angular/core';

@Component({
  selector: 'app-logo',
  
  template: `
    <div class="relative w-full h-full flex items-center justify-center group overflow-hidden">
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
  ]})
export class LogoComponent {}
