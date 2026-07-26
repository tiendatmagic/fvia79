import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-icon',
  
  templateUrl: './icon.component.html',
  
  host: {
    style: 'display: inline-flex; align-items: center; justify-content: center;'
  }
})
export class IconComponent {
    @Input() name: string = '';

    @Input() strokeWidth: string | number = '2';

    @Input() solid: boolean = false;

    @Input() viewBox: string = '0 0 24 24';
}
