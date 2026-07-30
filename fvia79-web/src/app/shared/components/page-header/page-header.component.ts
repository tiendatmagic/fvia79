import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-page-header',

  imports: [CommonModule],

  templateUrl: './page-header.component.html'})
export class PageHeaderComponent {
  @Input() title = '';
  @Input() description = '';
  @Input() containerClass = 'flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6';
  @Input() actionsClass = 'flex items-center gap-3 shrink-0 flex-wrap w-full xl:w-auto justify-start xl:justify-end';
}
