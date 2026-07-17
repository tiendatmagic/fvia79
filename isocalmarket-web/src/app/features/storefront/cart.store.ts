import { Injectable, signal, computed, effect } from '@angular/core';

export interface CartItem {
  id: number;
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

  constructor() {
    this.loadFromStorage();

    effect(() => {
      if (typeof window !== 'undefined') {
        localStorage.setItem('isocalmarket_cart', JSON.stringify(this.items()));
      }
    });
  }

  private loadFromStorage() {
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem('isocalmarket_cart');
      if (stored) {
        try {
          this.items.set(JSON.parse(stored));
        } catch (e) {
          this.items.set([]);
        }
      }
    }
  }

  public addToCart(product: any, quantity: number = 1) {
    const current = this.items();
    const index = current.findIndex(item => item.id === product.id);

    if (index > -1) {
      const updated = [...current];
      const newQty = updated[index].quantity + quantity;
      updated[index].quantity = Math.min(newQty, product.available_count || 1);
      this.items.set(updated);
    } else {
      this.items.set([...current, {
        id: product.id,
        name: product.name,
        price: product.price,
        quantity: Math.min(quantity, product.available_count || 1),
        available_count: product.available_count || 0
      }]);
    }
  }

  public removeFromCart(productId: number) {
    this.items.set(this.items().filter(item => item.id !== productId));
  }

  public updateQuantity(productId: number, quantity: number) {
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
