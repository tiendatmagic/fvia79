import { Component, Input, Output, EventEmitter, forwardRef, ElementRef, inject, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';
import { IconComponent } from '../icon/icon.component';

@Component({
  selector: 'app-custom-input',
  standalone: true,
  imports: [CommonModule, FormsModule, IconComponent],
  templateUrl: './custom-input.component.html',
  styleUrls: ['./custom-input.component.css'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => CustomInputComponent),
      multi: true
    }
  ]
})
export class CustomInputComponent implements ControlValueAccessor, AfterViewInit {
  private readonly elementRef = inject(ElementRef);
  private innerValue: any = '';

  @Input() type: 'text' | 'number' | 'email' | 'password' | 'textarea' | 'date' = 'text';
  @Input() placeholder: string = '';
  @Input() disabled: boolean = false;
  @Input() rows: number = 3;
  @Input() min: number | string = '';
  @Input() max: number | string = '';
  @Input() step: number | string = '';
  @Input() name: string = '';
  @Input() customClass: string = '';
  @Input() rightIcon?: string;
  @Input() rightIconTitle?: string;
  @Input() rightIconLoading?: boolean = false;

  public showPasswordToggle: boolean = false;

  public togglePassword(): void {
    this.showPasswordToggle = !this.showPasswordToggle;
  }

  @Output() valueChange = new EventEmitter<any>();
  @Output() rightIconClick = new EventEmitter<void>();

  public onRightIconClick(event: MouseEvent): void {
    event.preventDefault();
    event.stopPropagation();
    if (!this.disabled && !this.rightIconLoading) {
      this.rightIconClick.emit();
    }
  }

  private onChange: (value: any) => void = () => {};
  private onTouched: () => void = () => {};

  public ngAfterViewInit(): void {
    const inputEl = this.elementRef.nativeElement.querySelector('input, textarea') as HTMLInputElement | HTMLTextAreaElement;
    if (inputEl) {
      inputEl.value = this.innerValue;
    }
  }

  public onInput(event: Event): void {
    if (this.disabled) return;
    const rawValue = (event.target as HTMLInputElement | HTMLTextAreaElement).value;
    
    let modelValue: any = rawValue;
    if (this.type === 'number') {
      if (rawValue === '') {
        modelValue = null;
      } else {
        const parsed = Number(rawValue);
        modelValue = isNaN(parsed) ? rawValue : parsed;
      }
    }
    
    this.updateValue(rawValue, modelValue);
  }

  private updateValue(rawValue: any, modelValue: any): void {
    this.innerValue = rawValue;
    this.onChange(modelValue);
    this.onTouched();
    this.valueChange.emit(modelValue);
  }

  public writeValue(value: any): void {
    const normalizedValue = value === null || value === undefined ? '' : value;
    
    const inputEl = this.elementRef.nativeElement.querySelector('input, textarea') as HTMLInputElement | HTMLTextAreaElement;
    if (inputEl) {
      const currentValue = inputEl.value;
      if (this.areValuesEquivalent(currentValue, normalizedValue)) {
        this.innerValue = normalizedValue;
        return;
      }
      inputEl.value = normalizedValue;
    }
    
    this.innerValue = normalizedValue;
  }

  private areValuesEquivalent(val1: any, val2: any): boolean {
    if (val1 === val2) return true;
    
    const isEmpty1 = val1 === '' || val1 === null || val1 === undefined;
    const isEmpty2 = val2 === '' || val2 === null || val2 === undefined;
    if (isEmpty1 && isEmpty2) return true;
    
    if (this.type === 'number') {
      const n1 = val1 !== '' ? Number(val1) : NaN;
      const n2 = val2 !== '' ? Number(val2) : NaN;
      if (!isNaN(n1) && !isNaN(n2) && n1 === n2) {
        return true;
      }
    }
    
    return false;
  }

  public registerOnChange(fn: any): void {
    this.onChange = fn;
  }

  public registerOnTouched(fn: any): void {
    this.onTouched = fn;
  }

  public setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    const inputEl = this.elementRef.nativeElement.querySelector('input, textarea') as HTMLInputElement | HTMLTextAreaElement;
    if (inputEl) {
      inputEl.disabled = isDisabled;
    }
  }
}
