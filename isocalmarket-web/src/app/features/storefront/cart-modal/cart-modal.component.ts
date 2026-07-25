import { Component, inject, signal, OnInit, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { CartStore } from '../cart.store';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { LanguageService } from '@core/services/language.service';
import { CouponStore } from '../../admin-coupons/coupon.store';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { Subject, of } from 'rxjs';
import {
  debounceTime,
  distinctUntilChanged,
  switchMap,
  catchError,
  finalize,
} from 'rxjs/operators';

@Component({
  selector: 'app-cart-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ButtonComponent, IconComponent, TranslatePipe, CurrencyPipe],
  templateUrl: './cart-modal.component.html',
  styles: [
    `
      :host {
        display: block;
      }
    `,
  ],
})
export class CartModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly apiService = inject(ApiService);
  private readonly couponStore = inject(CouponStore);
  public readonly cart = inject(CartStore);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public readonly isSubmitting = signal(false);
  private checkoutIdempotencyKey = this.createIdempotencyKey();

  public couponCode = '';
  public couponError = signal<string | null>(null);
  public couponSuccess = signal<string | null>(null);
  public isValidatingCoupon = signal(false);
  public appliedCoupon = signal<any | null>(null);

  private readonly couponSubject = new Subject<string>();

  constructor() {
    effect(() => {
      const total = this.cart.totalAmount();
      if (this.couponCode && this.couponCode.trim()) {
        this.couponSubject.next(this.couponCode.trim());
      } else {
        this.appliedCoupon.set(null);
        this.couponError.set(null);
        this.couponSuccess.set(null);
      }
    });
  }

  ngOnInit() {
    this.couponSubject
      .pipe(
        debounceTime(500),
        distinctUntilChanged(),
        switchMap((code) => {
          this.couponError.set(null);
          this.couponSuccess.set(null);

          if (!code.trim()) {
            this.appliedCoupon.set(null);
            return of(null);
          }

          this.isValidatingCoupon.set(true);
          const total = this.cart.totalAmount();

          return this.couponStore.validateCoupon(code.trim(), total).pipe(
            catchError((err) => {
              const msg = err.message || this.lang.translate('cart.coupon_invalid');
              this.couponError.set(msg);
              this.appliedCoupon.set(null);
              return of(null);
            }),
            finalize(() => this.isValidatingCoupon.set(false)),
          );
        }),
      )
      .subscribe((res: any) => {
        if (res && res.valid) {
          this.appliedCoupon.set(res);
          const formatDiscount =
            res.type === 'percentage' ? `${res.value}%` : `${res.discount_amount}`;
          this.couponSuccess.set(this.lang.translate('cart.coupon_applied', { code: res.code }));
        }
      });
  }

  public onCouponInput(code: string) {
    this.couponSubject.next(code);
  }

  public get tierDiscountAmount(): number {
    const user = this.state.authStore.currentUser();
    const tier = user?.membership_tier;
    if (!tier || !tier.discount_percent || tier.discount_percent <= 0) return 0;
    const total = this.cart.totalAmount();
    return Math.round(total * (tier.discount_percent / 100) * 10000) / 10000;
  }

  public getFinalTotal(): number {
    const total = this.cart.totalAmount();
    const coupon = this.appliedCoupon();
    const couponDiscount = coupon ? (coupon.discount_amount || 0) : 0;
    return Math.max(0, total - this.tierDiscountAmount - couponDiscount);
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public checkout() {
    if (this.isSubmitting()) return;
    const cartItems = this.cart.items();
    if (cartItems.length === 0) return;

    if (!this.state.isAuthenticated()) {
      this.state.showToast(this.lang.translate('cart.please_login'), 'warning');
      this.modalRef.close('login');
      return;
    }

    this.isSubmitting.set(true);
    const itemsInput = cartItems.map((item) => ({
      product_id: item.id,
      quantity: item.quantity,
    }));

    const payload: any = {
      items: itemsInput,
      idempotency_key: this.checkoutIdempotencyKey,
    };
    if (this.couponCode.trim() && this.appliedCoupon()) {
      payload.coupon_code = this.couponCode.toUpperCase().trim();
    }

    this.apiService.post<any>('v1/orders', payload).subscribe({
      next: (res) => {
        if (res && res.user) {
          this.state.authStore.currentUser.set(res.user);
        }
        this.state.authStore.checkMe(true).subscribe();
        this.cart.clearCart();
        this.checkoutIdempotencyKey = this.createIdempotencyKey();
        this.state.orderStore.clearCache();
        const isPending = res?.order?.status === 'pending';
        this.state.showToast(
          this.lang.translate(isPending ? 'cart.order_pending_supplier' : 'cart.order_success'),
          isPending ? 'warning' : 'success',
        );
        this.isSubmitting.set(false);
        this.modalRef.close('success');
      },
      error: (err) => {
        const rawMsg = err.error?.message;
        const msg = typeof rawMsg === 'string' ? rawMsg : this.lang.translate('toast.order_failed');
        this.state.showToast(msg, 'error');
        this.isSubmitting.set(false);
      },
    });
  }

  private createIdempotencyKey(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
      return crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
      const random = Math.floor(Math.random() * 16);
      const value = character === 'x' ? random : (random & 0x3) | 0x8;
      return value.toString(16);
    });
  }
}
