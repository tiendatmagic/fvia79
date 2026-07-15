import { Injectable, inject, computed } from '@angular/core';
import { UiStateService } from './ui-state.service';
import { ThemeService } from './theme.service';
import { ToastService } from './toast.service';
import { AuthStore } from '../../features/auth/auth.store';
import { ProductStore } from '../../features/products/product.store';
import { InventoryStore } from '../../features/inventory/inventory.store';
import { OrderStore } from '../../features/orders/order.store';
import { Web3Service } from './web3.service';

/**
 * StateService đóng vai trò là Facade Pattern (giống như cafe-blockchain).
 * Lớp này chịu trách nhiệm tiêm các dịch vụ con và expose (ủy thác) các tín hiệu (signals),
 * thuộc tính cũng như phương thức cần thiết ra bên ngoài để các component sử dụng thống nhất.
 */
@Injectable({
  providedIn: 'root'
})
export class StateService {
  private readonly uiStateService = inject(UiStateService);
  private readonly themeService = inject(ThemeService);
  private readonly toastService = inject(ToastService);
  
  // Tiêm các Slice Stores mới
  public readonly authStore = inject(AuthStore);
  public readonly productStore = inject(ProductStore);
  public readonly inventoryStore = inject(InventoryStore);
  public readonly orderStore = inject(OrderStore);
  
  // Giữ Web3Service fallback để an toàn tương thích ngược
  private readonly web3Service = inject(Web3Service);

  // ====================================================
  // DELEGATED UI STATE (ỦY THÁC TRẠNG THÁI GIAO DIỆN)
  // ====================================================
  public readonly showMobileMenu = this.uiStateService.showMobileMenu;
  public readonly showDropdown = this.uiStateService.showDropdown;
  public readonly showNetworkDropdown = this.uiStateService.showNetworkDropdown;
  public readonly isLoading = computed(() => 
    this.uiStateService.isLoading() || 
    this.authStore.authLoading() || 
    this.productStore.loading() || 
    this.inventoryStore.loading() || 
    this.orderStore.loading()
  );

  // ====================================================
  // DELEGATED THEME STATE (ỦY THÁC TRẠNG THÁI THEME)
  // ====================================================
  public readonly isDarkMode = this.themeService.isDarkMode;
  public readonly themeMode = this.themeService.themeMode;

  // ====================================================
  // DELEGATED AUTH STATE (ỦY THÁC TRẠNG THÁI AUTH WEB2)
  // ====================================================
  public readonly currentUser = this.authStore.currentUser;
  public readonly isAuthenticated = this.authStore.isAuthenticated;
  public readonly isAdmin = this.authStore.isAdmin;

  // ====================================================
  // DELEGATED WEB3 STATE (MỌI HOẠT ĐỘNG WEB3 SẼ BỊ TẮT / KHÔNG KẾT NỐI VÌ ĐÂY LÀ WEB2)
  // ====================================================
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

  // ====================================================
  // DELEGATED METHODS (ỦY THÁC PHƯƠNG THỨC HÀNH ĐỘNG)
  // ====================================================

  // Thông báo Toast
  public showToast(msg: string, type: 'success' | 'error' | 'warning' = 'success') {
    this.toastService.showToast(msg, type);
  }

  // Thay đổi giao diện màu
  public toggleTheme() {
    this.themeService.toggleTheme();
  }

  public setThemeMode(mode: 'light' | 'dark' | 'auto') {
    this.themeService.setThemeMode(mode);
  }

  // Fallback các method wallet cũ tránh lỗi compile
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
