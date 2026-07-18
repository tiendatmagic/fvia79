import {
  Component,
  Input,
  Output,
  EventEmitter,
  ContentChildren,
  QueryList,
  TemplateRef,
  Directive,
  signal,
  computed
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';

export interface TableColumn {
  key: string;
  label: string;
  sortable?: boolean;
  align?: 'left' | 'center' | 'right';
  headerClass?: string;
  cellClass?: string;
}

@Directive({
  selector: '[appTableCell]',
  standalone: true
})
export class TableCellDirective {
  @Input('appTableCell') columnName!: string;

  constructor(public templateRef: TemplateRef<any>) {}
}

@Component({
  selector: 'app-table',
  standalone: true,
  imports: [CommonModule, IconComponent, SkeletonLoaderComponent],
  templateUrl: './table.component.html',
  styles: [
    `
      :host {
        display: block;
        width: 100%;
      }
    `
  ]
})
export class TableComponent {
  @Input() columns: TableColumn[] = [];
  @Input() loading: boolean = false;
  @Input() emptyText: string = 'Không có dữ liệu hiển thị';
  @Input() emptyIcon: string = 'search';
  @Input() hoverable: boolean = true;
  @Input() localSort: boolean = true;
  
  @Input() set data(value: any[]) {
    this._data.set(value || []);
  }
  get data(): any[] {
    return this._data();
  }

  @Input() set sortKey(val: string) {
    this.sortKeySignal.set(val);
  }
  get sortKey(): string {
    return this.sortKeySignal();
  }

  @Input() set sortDirection(val: 'asc' | 'desc' | '') {
    this.sortDirectionSignal.set(val);
  }
  get sortDirection(): 'asc' | 'desc' | '' {
    return this.sortDirectionSignal();
  }

  @Output() sortChange = new EventEmitter<{ key: string; direction: 'asc' | 'desc' | '' }>();

  @ContentChildren(TableCellDirective) cellTemplates!: QueryList<TableCellDirective>;

  private readonly _data = signal<any[]>([]);
  public readonly sortKeySignal = signal<string>('');
  public readonly sortDirectionSignal = signal<'asc' | 'desc' | ''>('');

  public readonly processedData = computed(() => {
    const list = [...this._data()];
    const key = this.sortKeySignal();
    const dir = this.sortDirectionSignal();

    if (this.localSort && key && dir) {
      list.sort((a, b) => {
        const valA = a[key];
        const valB = b[key];

        if (valA === undefined || valA === null) return dir === 'asc' ? 1 : -1;
        if (valB === undefined || valB === null) return dir === 'asc' ? -1 : 1;

        if (typeof valA === 'number' && typeof valB === 'number') {
          return dir === 'asc' ? valA - valB : valB - valA;
        }

        const strA = String(valA).toLowerCase();
        const strB = String(valB).toLowerCase();

        return dir === 'asc' 
          ? strA.localeCompare(strB, 'vi', { numeric: true }) 
          : strB.localeCompare(strA, 'vi', { numeric: true });
      });
    }
    return list;
  });

  public onHeaderClick(column: TableColumn): void {
    if (!column.sortable || this.loading) return;

    const currentKey = this.sortKeySignal();
    const currentDir = this.sortDirectionSignal();

    let nextDir: 'asc' | 'desc' | '' = 'asc';

    if (currentKey === column.key) {
      if (currentDir === 'asc') {
        nextDir = 'desc';
      } else if (currentDir === 'desc') {
        nextDir = '';
      } else {
        nextDir = 'asc';
      }
    }

    const nextKey = nextDir === '' ? '' : column.key;

    this.sortKeySignal.set(nextKey);
    this.sortDirectionSignal.set(nextDir);

    this.sortChange.emit({ key: nextKey, direction: nextDir });
  }

  public getTemplate(key: string): TemplateRef<any> | null {
    if (!this.cellTemplates) return null;
    const cell = this.cellTemplates.find((t) => t.columnName === key);
    return cell ? cell.templateRef : null;
  }
}
