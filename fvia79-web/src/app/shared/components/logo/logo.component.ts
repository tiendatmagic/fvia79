import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-logo',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <a
      [routerLink]="link ? link : null"
      [ngClass]="{
        'border border-slate-300/70 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 shadow-2xs hover:border-primary dark:hover:border-primary hover:shadow-xs hover:shadow-primary/10': showBorder,
        'cursor-pointer': !!link
      }"
      class="relative w-full h-full flex items-center justify-center p-0.5 rounded-xl transition-all duration-300 group overflow-hidden block"
    >
      <img
        src="assets/logo-dark.png"
        alt="Fvia79 Logo"
        class="w-full h-full object-contain scale-110 relative z-10 transition-transform duration-300 group-hover:scale-115 dark:hidden"
      />
      <img
        src="assets/logo-light.png"
        alt="Fvia79 Logo"
        class="w-full h-full object-contain scale-110 relative z-10 transition-transform duration-300 group-hover:scale-115 hidden dark:block"
      />
    </a>
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
  @Input() link: string | any[] | null = '/';
}
