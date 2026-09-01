import {
  Component,
  Input,
  Output,
  EventEmitter,
  ElementRef,
  ViewChildren,
  QueryList,
  AfterViewInit,
  ViewChild,
  signal,
  OnChanges,
  SimpleChanges,
  OnDestroy,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { Subscription } from 'rxjs';
import { IconComponent } from '@shared/components/icon/icon.component';

export interface TabOption {
  value: any;
  label: string;
  icon?: string;
  badge?: string | number;
  dotClass?: string;
}

@Component({
  selector: 'app-tab-group',
  standalone: true,
  host: {
    '(window:resize)': 'onResize()',
  },
  imports: [CommonModule, IconComponent],
  templateUrl: './tab-group.component.html',
  styles: [
    `
      :host {
        display: block;
      }
    `,
  ],
})
export class TabGroupComponent implements AfterViewInit, OnChanges, OnDestroy {
  @Input() options: TabOption[] = [];
  @Input() activeValue: any = null;
  @Input() variant: 'primary' | 'card' | 'soft' = 'primary';
  @Input() sliderClass: string = '';
  @Input() rounded: string = 'rounded-xl';
  @Input() containerClass: string = '';
  @Input() buttonClass: string = '';
  @Input() labelClass: string = '';
  @Input() flex: boolean = true;

  @Output() valueChange = new EventEmitter<any>();

  @ViewChild('containerEl') containerEl!: ElementRef<HTMLDivElement>;
  @ViewChildren('tabBtn') tabButtons!: QueryList<ElementRef<HTMLButtonElement>>;

  private resizeObserver?: ResizeObserver;
  private tabButtonsSub?: Subscription;
  public readonly isReady = signal(false);

  public readonly sliderStyle = signal<{ left: string; width: string }>({
    left: '0px',
    width: '0px',
  });

  public get activeIndex(): number {
    return this.options.findIndex((opt) => opt.value === this.activeValue);
  }

  ngAfterViewInit(): void {
    this.updateSliderPosition();
    requestAnimationFrame(() => this.updateSliderPosition());
    setTimeout(() => this.updateSliderPosition(), 50);
    setTimeout(() => this.updateSliderPosition(), 150);

    this.initResizeObserver();

    if (this.tabButtons) {
      this.tabButtonsSub = this.tabButtons.changes.subscribe(() => {
        this.updateSliderPosition();
        this.initResizeObserver();
      });
    }

    if (typeof document !== 'undefined' && document.fonts) {
      document.fonts.ready.then(() => {
        this.updateSliderPosition();
      });
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['activeValue'] || changes['options']) {
      this.updateSliderPosition();
      requestAnimationFrame(() => this.updateSliderPosition());
      setTimeout(() => this.updateSliderPosition(), 50);
    }
  }

  ngOnDestroy(): void {
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
    if (this.tabButtonsSub) {
      this.tabButtonsSub.unsubscribe();
    }
  }

  onResize(): void {
    this.updateSliderPosition();
  }

  private initResizeObserver(): void {
    if (typeof ResizeObserver === 'undefined') return;

    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }

    this.resizeObserver = new ResizeObserver(() => {
      this.updateSliderPosition();
    });

    if (this.containerEl?.nativeElement) {
      this.resizeObserver.observe(this.containerEl.nativeElement);
    }

    if (this.tabButtons) {
      this.tabButtons.forEach((btn) => {
        if (btn.nativeElement) {
          this.resizeObserver?.observe(btn.nativeElement);
        }
      });
    }
  }

  public onSelect(value: any): void {
    if (value !== this.activeValue) {
      this.activeValue = value;
      this.updateSliderPosition();
      this.valueChange.emit(value);
    }
  }

  public updateSliderPosition(): void {
    if (!this.tabButtons || !this.containerEl) return;

    const buttons = this.tabButtons.toArray();
    const activeIdx = this.activeIndex;

    if (activeIdx !== -1 && buttons[activeIdx]) {
      const activeEl = buttons[activeIdx].nativeElement;

      if (!activeEl || activeEl.offsetWidth === 0) {
        requestAnimationFrame(() => {
          if (this.tabButtons?.toArray()[activeIdx]?.nativeElement?.offsetWidth > 0) {
            this.updateSliderPosition();
          }
        });
        return;
      }

      const nextLeft = `${activeEl.offsetLeft}px`;
      const nextWidth = `${activeEl.offsetWidth}px`;

      const current = this.sliderStyle();
      if (current.left !== nextLeft || current.width !== nextWidth) {
        this.sliderStyle.set({ left: nextLeft, width: nextWidth });
      }

      if (!this.isReady()) {
        requestAnimationFrame(() => this.isReady.set(true));
      }

      const container = this.containerEl.nativeElement;
      const scrollLeft = container.scrollLeft;
      const containerWidth = container.clientWidth;
      const buttonLeft = activeEl.offsetLeft;
      const buttonWidth = activeEl.offsetWidth;

      if (buttonLeft < scrollLeft) {
        container.scrollTo({ left: Math.max(0, buttonLeft - 8), behavior: 'smooth' });
      } else if (buttonLeft + buttonWidth > scrollLeft + containerWidth) {
        container.scrollTo({ left: buttonLeft + buttonWidth - containerWidth + 8, behavior: 'smooth' });
      }
    } else {
      const current = this.sliderStyle();
      if (current.left !== '0px' || current.width !== '0px') {
        this.sliderStyle.set({ left: '0px', width: '0px' });
      }
    }
  }
}
