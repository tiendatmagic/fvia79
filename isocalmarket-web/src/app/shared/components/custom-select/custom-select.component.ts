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
  ChangeDetectorRef} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';
import { IconComponent } from '../icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

export interface SelectOption {
  [key: string]: any;
}

@Component({
  selector: 'app-custom-select',
  standalone: true,
  host: {
    '(document:click)': 'onClickOutside($event)'
  },

  imports: [CommonModule, FormsModule, IconComponent, TranslatePipe],
  templateUrl: './custom-select.component.html',

  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => CustomSelectComponent),
      multi: true},
  ],
  styles: [
    `
      :host {
        display: block;
      }
    `,
  ]})
export class CustomSelectComponent implements ControlValueAccessor, AfterViewChecked, OnInit, OnDestroy {
  private readonly elementRef = inject(ElementRef);
  private readonly cdr = inject(ChangeDetectorRef);
  private scrollListener: any;

  ngOnInit(): void {
    this.scrollListener = () => {
      if (this.isOpen()) {
        this.updateDropdownPosition();
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

  @Input() set value(val: any) {
    this._value = val;
    this.valueSignal.set(val);
  }
  get value(): any {
    return this._value;
  }
  private _value: any = null;
  @Output() valueChange = new EventEmitter<any>();

  public readonly optionsSignal = signal<any[]>([]);
  @Input() set options(val: any[]) {
    this.optionsSignal.set(val || []);
  }
  get options(): any[] {
    return this.optionsSignal();
  }

  public readonly loadingSignal = signal<boolean>(false);
  @Input() set loading(val: boolean) {
    this.loadingSignal.set(!!val);
  }
  get loading(): boolean {
    return this.loadingSignal();
  }

  @Input() valueKey: string = 'value';
  @Input() labelKey: string = 'label';
  @Input() placeholder: string = '';
  @Input() disabled: boolean = false;
  @Input() placement: 'bottom' | 'top' | 'auto' = 'auto';
  @Input() showSearch: boolean = false;
  @Input() containerClass: string = 'w-full';
  @Input() triggerClass: string = 'w-full form-input';
  @Input() maxDisplay: number = 50;

  public readonly valueSignal = signal<any>(null);

  @ViewChild('triggerBtn', { static: false }) triggerBtn!: ElementRef<HTMLButtonElement>;

  public readonly isOpen = signal<boolean>(false);
  public readonly searchQuery = signal<string>('');
  public readonly displayLimit = signal<number>(50);

  public dropdownStyle: { [key: string]: string } = {};
  public resolvedPlacement: 'top' | 'bottom' = 'bottom';

  public onClickOutside(event: MouseEvent): void {
    if (!this.elementRef.nativeElement.contains(event.target)) {
      this.isOpen.set(false);
    }
  }

  public toggleOpen(): void {
    if (this.disabled) return;
    const nextState = !this.isOpen();
    if (nextState) {
      this.searchQuery.set('');
      this.displayLimit.set(this.maxDisplay || 50);
      this.updateDropdownPosition();
    }
    this.isOpen.set(nextState);
  }

  public onSearchInput(query: string): void {
    this.searchQuery.set(query);
    this.displayLimit.set(this.maxDisplay || 50);
  }

  public onDropdownScroll(event: Event): void {
    const el = event.target as HTMLElement;
    if (!el) return;
    const scrollBottom = el.scrollTop + el.clientHeight;
    const scrollThreshold = el.scrollHeight * 0.5;
    if (scrollBottom >= scrollThreshold && this.displayLimit() < this.filteredOptions().length) {
      this.displayLimit.update(limit => limit + (this.maxDisplay || 50));
    }
  }

    private updateDropdownPosition(): void {
    const triggerEl = this.triggerBtn?.nativeElement;
    if (!triggerEl) return;

    const rect = triggerEl.getBoundingClientRect();
    const dropdownMaxHeight = 280;
    const gap = 6;

    const spaceBelow = window.innerHeight - rect.bottom - gap;
    const spaceAbove = rect.top - gap;

    let placeFinal: 'top' | 'bottom';
    if (this.placement === 'top') {
      placeFinal = 'top';
    } else if (this.placement === 'bottom') {
      placeFinal = 'bottom';
    } else {
      if (spaceBelow >= dropdownMaxHeight) {
        placeFinal = 'bottom';
      } else if (spaceAbove >= dropdownMaxHeight) {
        placeFinal = 'top';
      } else {
        placeFinal = spaceBelow >= spaceAbove ? 'bottom' : 'top';
      }
    }

    this.resolvedPlacement = placeFinal;

    if (placeFinal === 'bottom') {
      this.dropdownStyle = {
        position: 'fixed',
        top: `${rect.bottom + gap}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        maxHeight: `${Math.min(dropdownMaxHeight, spaceBelow)}px`,
        zIndex: '9999',
      };
    } else {
      this.dropdownStyle = {
        position: 'fixed',
        top: `${rect.top - gap}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        maxHeight: `${Math.min(dropdownMaxHeight, spaceAbove)}px`,
        transform: 'translateY(-100%)',
        zIndex: '9999',
      };
    }
  }
  ngAfterViewChecked(): void {
    if (this.isOpen() && this.triggerBtn) {
      this.updateDropdownPosition();
    }
  }

  @HostListener('window:scroll')
  @HostListener('window:resize')
  onWindowChange(): void {
    if (this.isOpen()) {
      this.updateDropdownPosition();
    }
  }

  public selectOption(option: any): void {
    const val = this.getOptionValue(option);
    this.value = val;
    this.valueChange.emit(val);
    this.onChange(val);
    this.onTouched();
    this.isOpen.set(false);
  }

  public readonly selectedLabel = computed(() => {
    const opts = this.optionsSignal();
    const val = this.valueSignal();
    const selected = opts.find((opt) => {
      const optVal = this.getOptionValue(opt);
      return String(optVal) === String(val);
    });
    return selected !== undefined ? this.getOptionLabel(selected) : '';
  });

  public getOptionValue(option: any): any {
    if (option && typeof option === 'object' && this.valueKey) {
      return option[this.valueKey];
    }
    return option;
  }

  public getOptionLabel(option: any): string {
    if (option && typeof option === 'object' && this.labelKey) {
      return option[this.labelKey];
    }
    return option;
  }

  public compareValues(val1: any, val2: any): boolean {
    if (val1 === null || val1 === undefined) return val2 === null || val2 === undefined;
    if (val2 === null || val2 === undefined) return false;
    return String(val1) === String(val2);
  }

  public readonly filteredOptions = computed(() => {
    const opts = this.optionsSignal();
    const query = this.searchQuery().trim().toLowerCase();
    if (!query) return opts;
    return opts.filter((opt) => {
      const label = String(this.getOptionLabel(opt)).toLowerCase();
      return label.includes(query);
    });
  });

  public readonly shouldShowSearch = computed(() => {
    return this.showSearch || (this.optionsSignal() || []).length > 8;
  });

  public readonly displayedOptions = computed(() => {
    const filtered = this.filteredOptions();
    const limit = this.displayLimit();
    if (filtered.length <= limit) {
      return filtered;
    }
    return filtered.slice(0, limit);
  });

  private onChange: (value: any) => void = () => {};
  private onTouched: () => void = () => {};

  public writeValue(value: any): void {
    this.value = value;
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
