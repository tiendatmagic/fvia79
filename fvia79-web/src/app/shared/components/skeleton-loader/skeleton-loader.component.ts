import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-skeleton-loader',
  
  imports: [CommonModule],
  
  templateUrl: './skeleton-loader.component.html'})
export class SkeletonLoaderComponent {
  @Input() type:
    | 'dashboard'
    | 'table'
    | 'card-grid'
    | 'form'
    | 'list'
    | 'membership-tier' = 'table';
  @Input() rows = 5;
  @Input() cols = 5;
  @Input() cards = 8;
  @Input() showHeader = true;
  @Input() showImage = true;
  @Input() buttonLayout: 'full' | 'split' | 'end' = 'split';
  getArray(count: number): number[] {
    return Array.from({ length: count }, (_, i) => i + 1);
  }
}
