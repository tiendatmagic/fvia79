import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalRef } from '@core/services/modal-ref';
import { CartStore } from '../cart.store';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { ButtonComponent } from '@shared/components/button/button.component';

@Component({
  selector: 'app-cart-modal',
  standalone: true,
  imports: [
    CommonModule,
    ButtonComponent
  ],
  templateUrl: './cart-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class CartModalComponent {
  private readonly modalRef = inject(ModalRef);
  private readonly apiService = inject(ApiService);
  public readonly cart = inject(CartStore);
  public readonly state = inject(StateService);

  public isSubmitting = false;

  public onClose() {
    this.modalRef.close(false);
  }

  public checkout() {
    const cartItems = this.cart.items();
    if (cartItems.length === 0) return;

    if (!this.state.isAuthenticated()) {
      this.state.showToast('Vui lòng đăng nhập để đặt hàng!', 'warning');
      this.modalRef.close('login'); // Trả về 'login' để storefront redirect
      return;
    }

    this.isSubmitting = true;
    const itemsInput = cartItems.map(item => ({
      product_id: item.id,
      quantity: item.quantity
    }));

    this.apiService.post<any>('v1/orders', { items: itemsInput }).subscribe({
      next: (res) => {
        this.cart.clearCart();
        this.state.showToast('Đặt hàng thành công! Đơn hàng đang chờ thanh toán.', 'success');
        this.isSubmitting = false;
        this.modalRef.close('success'); // Trả về 'success' để storefront redirect
      },
      error: (err) => {
        this.state.showToast(err.error?.message || 'Có lỗi xảy ra khi tạo đơn hàng!', 'error');
        this.isSubmitting = false;
      }
    });
  }
}
