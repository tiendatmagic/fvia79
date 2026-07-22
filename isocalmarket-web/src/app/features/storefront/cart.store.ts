import { Injectable, signal, computed } from '@angular/core';

export interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  available_count: number;
}

@Injectable({
  providedIn: 'root'
})
export class CartStore {
  public readonly items = signal<CartItem[]>([]);

  public readonly totalAmount = computed(() => {
    return this.items().reduce((total, item) => total + (item.price * item.quantity), 0);
  });

  public readonly totalItems = computed(() => {
    return this.items().reduce((total, item) => total + item.quantity, 0);
  });

  constructor() {}

  public setSingleProduct(product: any, quantity: number = 1) {
    const maxQty = product.available_count || 1;
    const finalQty = Math.min(Math.max(1, quantity), maxQty);
    this.items.set([{
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: finalQty,
      available_count: product.available_count || 0
    }]);
  }

  public addToCart(product: any, quantity: number = 1) {
    this.setSingleProduct(product, quantity);
  }

  public removeFromCart(productId: string) {
    this.items.set(this.items().filter(item => item.id !== productId));
  }

  public updateQuantity(productId: string, quantity: number) {
    const current = this.items();
    const index = current.findIndex(item => item.id === productId);
    if (index > -1 && quantity > 0) {
      const updated = [...current];
      updated[index].quantity = Math.min(quantity, updated[index].available_count);
      this.items.set(updated);
    }
  }

  public clearCart() {
    this.items.set([]);
  }
}

