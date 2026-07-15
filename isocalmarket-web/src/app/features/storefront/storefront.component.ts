import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { CartStore } from './cart.store';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';

@Component({
  selector: 'app-storefront',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    SkeletonLoaderComponent,
    CustomSearchInputComponent,
    ButtonComponent,
    PaginationComponent
  ],
  templateUrl: './storefront.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class StorefrontComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly cart = inject(CartStore);
  private readonly router = inject(Router);

  public searchQuery = '';
  public showCartDrawer = false;
  
  public readonly currentPage = signal<number>(1);
  public readonly limit = 12;

  // Tìm kiếm sản phẩm
  public readonly filteredProducts = computed(() => {
    const list = this.state.productStore.products();
    const q = this.searchQuery.toLowerCase().trim();
    if (!q) return list;
    return list.filter(p => 
      p.name.toLowerCase().includes(q) || 
      (p.description && p.description.toLowerCase().includes(q))
    );
  });

  ngOnInit() {
    this.fetchPage(1);
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.productStore.fetchProducts(page, this.limit).subscribe();
  }


  public addToCart(product: any) {
    if (product.available_count <= 0) {
      this.state.showToast('Sản phẩm hiện đang hết hàng!', 'warning');
      return;
    }
    this.cart.addToCart(product, 1);
    this.state.showToast(`Đã thêm '${product.name}' vào giỏ hàng!`, 'success');
  }

  public handleCheckout() {
    if (!this.state.isAuthenticated()) {
      this.state.showToast('Vui lòng đăng nhập để thực hiện thanh toán!', 'warning');
      this.router.navigate(['/auth/login'], { queryParams: { returnUrl: '/storefront' } });
      return;
    }

    const cartItems = this.cart.items();
    if (cartItems.length === 0) {
      this.state.showToast('Giỏ hàng trống!', 'warning');
      return;
    }

    const itemsInput = cartItems.map(item => ({
      product_id: item.id,
      quantity: item.quantity
    }));

    this.state.orderStore.createOrder(itemsInput).subscribe((res) => {
      if (res) {
        this.cart.clearCart();
        this.showCartDrawer = false;
        this.router.navigate(['/orders']);
      }
    });
  }
}
