import { Component, inject, signal, OnInit, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { CartStore } from '../cart.store';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { CouponStore } from '../../admin-coupons/coupon.store';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { Subject, of } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, catchError, finalize } from 'rxjs/operators';

@Component({
  selector: 'app-cart-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    IconComponent
  ],
  templateUrl: './cart-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class CartModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly apiService = inject(ApiService);
  private readonly couponStore = inject(CouponStore);
  public readonly cart = inject(CartStore);
  public readonly state = inject(StateService);

  public readonly isSubmitting = signal(false);

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
    this.couponSubject.pipe(
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
            const msg = err.message || 'Mã giảm giá không hợp lệ!';
            this.couponError.set(msg);
            this.appliedCoupon.set(null);
            return of(null);
          }),
          finalize(() => this.isValidatingCoupon.set(false))
        );
      })
    ).subscribe((res: any) => {
      if (res && res.valid) {
        this.appliedCoupon.set(res);
        const formatDiscount = res.type === 'percentage'
          ? `${res.value}%`
          : `${this.formatNumber(res.discount_amount)} VND`;
        this.couponSuccess.set(`Đã áp dụng mã "${res.code}". Giảm ${formatDiscount}!`);
      }
    });
  }

  public onCouponInput(code: string) {
    this.couponSubject.next(code);
  }

  public formatNumber(val: number): string {
    return new Intl.NumberFormat('vi-VN').format(val);
  }

  public getFinalTotal(): number {
    const total = this.cart.totalAmount();
    const coupon = this.appliedCoupon();
    if (!coupon) return total;
    return Math.max(0, total - (coupon.discount_amount || 0));
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public checkout() {
    if (this.isSubmitting()) return;
    const cartItems = this.cart.items();
    if (cartItems.length === 0) return;

    if (!this.state.isAuthenticated()) {
      this.state.showToast('Vui lòng đăng nhập để đặt hàng!', 'warning');
      this.modalRef.close('login');
      return;
    }

    this.isSubmitting.set(true);
    const itemsInput = cartItems.map(item => ({
      product_id: item.id,
      quantity: item.quantity
    }));

    const payload: any = { items: itemsInput };
    if (this.couponCode.trim() && this.appliedCoupon()) {
      payload.coupon_code = this.couponCode.toUpperCase().trim();
    }

    this.apiService.post<any>('v1/orders', payload).subscribe({
      next: (res) => {
        this.cart.clearCart();
        this.state.showToast('Đặt hàng thành công! Đơn hàng đang chờ thanh toán.', 'success');
        this.isSubmitting.set(false);
        this.modalRef.close('success');
      },
      error: (err) => {
        const rawMsg = err.error?.message;
        let msg = typeof rawMsg === 'string' ? rawMsg : 'Có lỗi xảy ra khi tạo đơn hàng!';
        if (msg.includes('Product is not available or has been discontinued')) {
          msg = 'Sản phẩm không khả dụng hoặc đã bị ngừng kinh doanh. Vui lòng kiểm tra lại giỏ hàng!';
        } else if (msg.includes('does not have enough stock available')) {
          msg = 'Một hoặc nhiều sản phẩm trong giỏ hàng không đủ số lượng tồn kho khả dụng!';
        }
        this.state.showToast(msg, 'error');
        this.isSubmitting.set(false);
      }
    });
  }
}
