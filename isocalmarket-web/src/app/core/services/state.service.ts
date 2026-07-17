import { Injectable, inject, computed } from '@angular/core';
import { UiStateService } from './ui-state.service';
import { ThemeService } from './theme.service';
import { ToastService } from './toast.service';
import { AuthStore } from '../../features/auth/auth.store';
import { ProductStore } from '../../features/products/product.store';
import { InventoryStore } from '../../features/inventory/inventory.store';
import { OrderStore } from '../../features/orders/order.store';
import { Web3Service } from './web3.service';

@Injectable({
  providedIn: 'root'
})
export class StateService {
  private readonly uiStateService = inject(UiStateService);
  private readonly themeService = inject(ThemeService);
  private readonly toastService = inject(ToastService);
  
  public readonly authStore = inject(AuthStore);
  public readonly productStore = inject(ProductStore);
  public readonly inventoryStore = inject(InventoryStore);
  public readonly orderStore = inject(OrderStore);
  
  private readonly web3Service = inject(Web3Service);

  public readonly showMobileMenu = this.uiStateService.showMobileMenu;
  public readonly showDropdown = this.uiStateService.showDropdown;
  public readonly showNetworkDropdown = this.uiStateService.showNetworkDropdown;
  public readonly isMaintenance = this.uiStateService.isMaintenance;
  public readonly isLoading = computed(() => 
    this.uiStateService.isLoading() || 
    this.authStore.authLoading() || 
    this.productStore.loading() || 
    this.inventoryStore.loading() || 
    this.orderStore.loading()
  );

  public readonly isDarkMode = this.themeService.isDarkMode;
  public readonly themeMode = this.themeService.themeMode;

  public readonly currentUser = this.authStore.currentUser;
  public readonly isAuthenticated = this.authStore.isAuthenticated;
  public readonly isAdmin = this.authStore.isAdmin;

  public readonly address = computed(() => null);
  public readonly chainId = computed(() => null);
  public readonly isConnected = computed(() => false);
  public readonly balance = computed(() => '0.0000');
  public readonly networkName = computed(() => 'Web2 Mode');
  public readonly isWrongChain = computed(() => false);
  public readonly chainSymbol = computed(() => 'VND');
  public readonly txSpeed = this.web3Service.txSpeed;
  public readonly gasMultiplier = this.web3Service.gasMultiplier;
  public readonly showWrongChainModal = computed(() => false);
  public readonly POPULAR_CHAINS = this.web3Service.POPULAR_CHAINS;
  public readonly shortenedAddress = computed(() => '');

  public showToast(msg: string, type: 'success' | 'error' | 'warning' = 'success') {
    this.toastService.showToast(msg, type);
  }

  public toggleTheme() {
    this.themeService.toggleTheme();
  }

  public setThemeMode(mode: 'light' | 'dark' | 'auto') {
    this.themeService.setThemeMode(mode);
  }

  public async connectWallet() {
    this.showToast('Hệ thống hiện đang chạy chế độ Web2, vui lòng đăng nhập bằng tài khoản!', 'warning');
  }

  public async openNetworkModal() {}
  public async openAccountModal() {}
  public async disconnectWallet() {}
  public async switchNetwork(chainId: number) {}
  public async getSigner() {
    throw new Error('Web3 is disabled in Web2 mode.');
  }
  public getProvider() {
    throw new Error('Web3 is disabled in Web2 mode.');
  }
  public async getGasOverrides(signer?: any) {
    return {};
  }
}
