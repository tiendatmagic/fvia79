import { Component, Input, ContentChildren, QueryList, AfterContentInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AccordionItemComponent } from './accordion-item.component';

@Component({
  selector: 'app-accordion',
  imports: [CommonModule],
  templateUrl: './accordion.component.html',
  styles: [
    `
      :host {
        display: block;
      }
    `,
  ],
})
export class AccordionComponent implements AfterContentInit {
    @Input() multiple = false;

  @ContentChildren(AccordionItemComponent) items!: QueryList<AccordionItemComponent>;

  ngAfterContentInit(): void {
    if (!this.multiple) {
      let alreadyExpanded = false;
      this.items.forEach(item => {
        if (item.expanded) {
          if (alreadyExpanded) {
            item.expanded = false;
          } else {
            alreadyExpanded = true;
          }
        }
      });
    }
  }

  public toggleItem(targetItem: AccordionItemComponent): void {
    if (this.multiple) {
      targetItem.expanded = !targetItem.expanded;
      targetItem.expandedChange.emit(targetItem.expanded);
    } else {
      const isCurrentlyExpanded = targetItem.expanded;
      
      this.items.forEach(item => {
        if (item === targetItem) {
          item.expanded = !isCurrentlyExpanded;
          item.expandedChange.emit(item.expanded);
        } else {
          if (item.expanded) {
            item.expanded = false;
            item.expandedChange.emit(false);
          }
        }
      });
    }
  }
}
