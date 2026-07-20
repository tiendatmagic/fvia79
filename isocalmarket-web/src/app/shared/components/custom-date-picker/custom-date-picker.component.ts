import {
  Component,
  Input,
  Output,
  EventEmitter,
  ElementRef,
  HostListener,
  inject,
  signal,
  computed,
  forwardRef,
  ViewChild,
  AfterViewChecked,
  OnInit,
  OnDestroy,
  ChangeDetectorRef
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';
import { IconComponent } from '../icon/icon.component';
import { LanguageService } from '@core/services/language.service';

@Component({
  selector: 'app-custom-date-picker',
  host: {
    '(document:click)': 'onClickOutside($event)'
  },
  imports: [CommonModule, FormsModule, IconComponent],
  templateUrl: './custom-date-picker.component.html',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => CustomDatePickerComponent),
      multi: true
    }
  ],
  styles: [
    `
      :host {
        display: block;
      }
    `
  ]
})
export class CustomDatePickerComponent implements ControlValueAccessor, AfterViewChecked, OnInit, OnDestroy {
  private readonly elementRef = inject(ElementRef);
  private readonly cdr = inject(ChangeDetectorRef);
  public readonly lang = inject(LanguageService);
  private scrollListener: any;

  ngOnInit(): void {
    this.scrollListener = () => {
      if (this.isOpen()) {
        this.updatePopoverPosition();
        this.cdr.detectChanges();
      }
    };
    window.addEventListener('scroll', this.scrollListener, true);
  }

  ngOnDestroy(): void {
    if (this.scrollListener) {
      window.removeEventListener('scroll', this.scrollListener, true);
    }
  }

  @Input() placeholder: string = '';
  @Input() disabled: boolean = false;
  @Input() minDate: string = '';
  @Input() maxDate: string = '';
  @Input() showPresets: boolean = true;

  @Output() valueChange = new EventEmitter<string>();

  @ViewChild('triggerDiv', { static: false }) triggerDiv!: ElementRef<HTMLDivElement>;

  public readonly value = signal<string>('');
  public readonly isOpen = signal<boolean>(false);

  public readonly currentYear = signal<number>(new Date().getFullYear());
  public readonly currentMonth = signal<number>(new Date().getMonth());

  public get weekdays(): string[] {
    return this.lang.currentLang() === 'vi'
      ? ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
      : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  }

  public popoverStyle: { [key: string]: string } = {};

  public get presets() {
    return [
      { label: this.lang.translate('date.days_7'), days: 7 },
      { label: this.lang.translate('date.month_1'), days: 30 },
      { label: this.lang.translate('date.months_3'), days: 90 },
      { label: this.lang.translate('date.months_6'), days: 180 },
      { label: this.lang.translate('date.year_1'), days: 365 },
    ];
  }

  public readonly displayValue = computed(() => {
    const val = this.value();
    if (!val) return '';
    const parts = val.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return val;
  });

  public readonly calendarDays = computed(() => {
    const year = this.currentYear();
    const month = this.currentMonth();
    const firstDay = new Date(year, month, 1);
    const startDayOfWeek = firstDay.getDay();
    const offset = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;
    const startDate = new Date(year, month, 1 - offset);

    const daysArray: Date[] = [];
    for (let i = 0; i < 42; i++) {
      daysArray.push(new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + i));
    }
    return daysArray;
  });

  public readonly currentMonthName = computed(() => {
    this.lang.currentLang();
    return new Intl.DateTimeFormat(this.lang.currentLang(), { month: 'long' })
      .format(new Date(this.currentYear(), this.currentMonth(), 1));
  });

  public onClickOutside(event: MouseEvent): void {
    if (!this.elementRef.nativeElement.contains(event.target)) {
      const popover = document.querySelector('.date-picker-popover');
      if (popover && popover.contains(event.target as Node)) return;
      this.isOpen.set(false);
    }
  }

  public toggleOpen(): void {
    if (this.disabled) return;
    const nextState = !this.isOpen();
    this.isOpen.set(nextState);
    if (nextState) {
      const dateVal = this.parseDate(this.value());
      const baseDate = dateVal || new Date();
      this.currentYear.set(baseDate.getFullYear());
      this.currentMonth.set(baseDate.getMonth());
      this.updatePopoverPosition();
    }
  }

  private updatePopoverPosition(): void {
    const triggerEl = this.triggerDiv?.nativeElement;
    if (!triggerEl) return;

    const rect = triggerEl.getBoundingClientRect();
    const popoverHeight = this.showPresets ? 380 : 340;
    const popoverWidth = 300;
    const gap = 6;

    const spaceBelow = window.innerHeight - rect.bottom - gap;
    const spaceAbove = rect.top - gap;

    let placeBottom = true;
    if (spaceBelow >= popoverHeight) {
      placeBottom = true;
    } else if (spaceAbove >= popoverHeight) {
      placeBottom = false;
    } else {
      placeBottom = spaceBelow >= spaceAbove;
    }

    let left = rect.left;
    if (left + popoverWidth > window.innerWidth - 8) {
      left = rect.right - popoverWidth;
    }
    if (left < 8) left = 8;

    let top = 0;
    if (placeBottom) {
      top = rect.bottom + gap;
      if (top + popoverHeight > window.innerHeight - 8) {
        top = window.innerHeight - 8 - popoverHeight;
      }
      if (top < 8) top = 8;
    } else {
      top = rect.top - gap - popoverHeight;
      if (top < 8) {
        top = 8;
      }
      if (top + popoverHeight > window.innerHeight - 8) {
        top = window.innerHeight - 8 - popoverHeight;
      }
    }

    this.popoverStyle = {
      position: 'fixed',
      top: `${top}px`,
      left: `${left}px`,
      width: `${popoverWidth}px`,
      zIndex: '9999',
    };
  }

  ngAfterViewChecked(): void {
    if (this.isOpen() && this.triggerDiv) {
      this.updatePopoverPosition();
    }
  }

  @HostListener('window:scroll')
  @HostListener('window:resize')
  onWindowChange(): void {
    if (this.isOpen()) {
      this.updatePopoverPosition();
    }
  }

  public prevMonth(event: Event): void {
    event.stopPropagation();
    const current = this.currentMonth();
    if (current === 0) {
      this.currentMonth.set(11);
      this.currentYear.update(y => y - 1);
    } else {
      this.currentMonth.set(current - 1);
    }
  }

  public nextMonth(event: Event): void {
    event.stopPropagation();
    const current = this.currentMonth();
    if (current === 11) {
      this.currentMonth.set(0);
      this.currentYear.update(y => y + 1);
    } else {
      this.currentMonth.set(current + 1);
    }
  }

  public selectDate(date: Date, event: Event): void {
    event.stopPropagation();
    if (this.isDateDisabled(date)) return;

    const formatted = this.formatDate(date);
    this.value.set(formatted);
    this.valueChange.emit(formatted);
    this.onChange(formatted);
    this.onTouched();
    this.isOpen.set(false);
  }

    public selectPreset(days: number, event: Event): void {
    event.stopPropagation();
    const target = new Date();
    target.setDate(target.getDate() + days);
    const formatted = this.formatDate(target);
    this.value.set(formatted);
    this.valueChange.emit(formatted);
    this.onChange(formatted);
    this.onTouched();
    this.isOpen.set(false);
  }

  public isToday(date: Date): boolean {
    const today = new Date();
    return date.getDate() === today.getDate() &&
      date.getMonth() === today.getMonth() &&
      date.getFullYear() === today.getFullYear();
  }

  public isSelected(date: Date): boolean {
    const val = this.value();
    if (!val) return false;
    return val === this.formatDate(date);
  }

  public isCurrentMonth(date: Date): boolean {
    return date.getMonth() === this.currentMonth();
  }

    public isDateDisabled(date: Date): boolean {
    if (this.minDate) {
      const min = this.parseDate(this.minDate);
      if (min) {
        const minDay = new Date(min.getFullYear(), min.getMonth(), min.getDate());
        const checkDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        if (checkDay.getTime() < minDay.getTime()) return true;
      }
    }
    if (this.maxDate) {
      const max = this.parseDate(this.maxDate);
      if (max) {
        const maxDay = new Date(max.getFullYear(), max.getMonth(), max.getDate());
        const checkDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        if (checkDay.getTime() > maxDay.getTime()) return true;
      }
    }
    return false;
  }

  private formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  private parseDate(str: string): Date | null {
    if (!str) return null;
    const parts = str.split('-');
    if (parts.length === 3) {
      const year = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10) - 1;
      const day = parseInt(parts[2], 10);
      const d = new Date(year, month, day);
      if (!isNaN(d.getTime())) return d;
    }
    return null;
  }
  public static todayString(): string {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  }

  private onChange: (value: any) => void = () => {};
  private onTouched: () => void = () => {};

  public writeValue(value: any): void {
    this.value.set(value ? String(value) : '');
  }

  public registerOnChange(fn: any): void {
    this.onChange = fn;
  }

  public registerOnTouched(fn: any): void {
    this.onTouched = fn;
  }

  public setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }
}
