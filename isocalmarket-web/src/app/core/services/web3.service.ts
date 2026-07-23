import { Injectable, signal, inject, effect } from '@angular/core';
import type { AppKit } from '@reown/appkit';
import { environment } from '@environments/environment';
import { ThemeService } from './theme.service';
import { ToastService } from './toast.service';
import { LanguageService } from './language.service';
import { POPULAR_CHAINS } from '../utils/blockchain.utils';

@Injectable({
  providedIn: 'root'
})
export class Web3Service {
  private modal!: AppKit;

  public address = signal<string | null>(null);
  public chainId = signal<number | null>(null);
  public isConnected = signal<boolean>(false);
  public balance = signal<string>('0.0000');
  public chainSymbol = signal<string>('ETH');
  public networkName = signal<string>('Unknown Network');
  public isWrongChain = signal<boolean>(false);
  public txSpeed = signal<'default' | 'fast' | 'custom'>('default');
  public gasMultiplier = signal<number>(2);
  public showWrongChainModal = signal<boolean>(false);

  public readonly POPULAR_CHAINS = POPULAR_CHAINS;

  public supportedChains: any[] = POPULAR_CHAINS.map(c => ({
    id: Number(c.chainId),
    name: c.name,
    explorerUrl: c.explorerUrl,
    rpcUrl: c.rpcUrl
  }));

  private readonly themeService = inject(ThemeService);
  private readonly toastService = inject(ToastService);
  private readonly lang = inject(LanguageService);

  constructor() {
    if (environment.enableWeb3) {
      this.initAppKit();
      this.setupThemeSync();
    }
  }

  private setupThemeSync() {
    effect(() => {
      const isDark = this.themeService.isDarkMode();
      if (this.modal) {
        try {
          this.modal.setThemeMode(isDark ? 'dark' : 'light');
        } catch (e) {
          console.warn('[Web3] Failed to sync theme to AppKit:', e);
        }
      }
    });
  }

  private async initAppKit() {
    if (typeof window === 'undefined') return;

    const projectId = environment.walletConnectProjectId;
    if (!projectId) {
      console.warn('[Web3] walletConnectProjectId is missing in environment configuration.');
      return;
    }

    try {
      const [{ createAppKit }, { EthersAdapter }, networks] = await Promise.all([
        import('@reown/appkit'),
        import('@reown/appkit-adapter-ethers'),
        import('@reown/appkit/networks')
      ]);

      const { mainnet, arbitrum, arbitrumSepolia, bsc, bscTestnet } = networks;
      const chains = [arbitrum, mainnet, bsc, arbitrumSepolia, bscTestnet].map(chain => {
        const popular = POPULAR_CHAINS.find(c => Number(c.chainId) === Number(chain.id));
        if (popular) {
          return {
            ...chain,
            rpcUrls: {
              ...chain.rpcUrls,
              default: { http: [popular.rpcUrl] }
            },
            blockExplorers: {
              ...chain.blockExplorers,
              default: { name: popular.name, url: popular.explorerUrl }
            }
          };
        }
        return chain;
      });

      this.supportedChains = chains;
      const isDark = this.themeService.isDarkMode();

      this.modal = createAppKit({
        adapters: [new EthersAdapter()],
        networks: chains as any,
        defaultNetwork: chains[0] as any,
        allowUnsupportedChain: true,
        metadata: {
          name: 'Isocal Market',
          description: this.lang.translate('app.tagline'),
          url: window.location.origin,
          icons: [window.location.origin + '/assets/logo-light.png']
        },
        projectId,
        themeMode: isDark ? 'dark' : 'light',
        defaultAccountTypes: {
          eip155: 'smartAccount'
        },
        features: {
          analytics: false,
          reownAuthentication: false
        },
        enableCoinbase: false
      } as any);

      this.modal.subscribeAccount(async (accountState) => {
        const prevConnected = this.isConnected();
        this.address.set(accountState.address || null);
        this.isConnected.set(accountState.isConnected);

        if (accountState.isConnected && accountState.address) {
          await this.updateBalanceAndNetwork();
          
          const currentChainId = this.chainId();
          if (currentChainId) {
            this.checkAndUpdateNetworkState(currentChainId, false);
          }
        } else {
          this.balance.set('0.0000');
          this.chainId.set(null);
          this.networkName.set('Unknown Network');
          this.isWrongChain.set(false);
          this.showWrongChainModal.set(false);
          if (prevConnected) {
            this.toastService.showToast(this.lang.translate('toast.wallet_disconnected'), 'error');
          }
        }
      });

      this.modal.subscribeNetwork((networkState) => {
        if (networkState.chainId) {
          const id = Number(networkState.chainId);
          this.checkAndUpdateNetworkState(id, true);
        }

        if (this.isConnected()) {
          this.updateBalanceAndNetwork();
        }
      });
    } catch (e) {
      console.error('[Web3] Failed to initialize AppKit asynchronously:', e);
    }
  }

  private checkAndUpdateNetworkState(chainId: number, showToastAlert = true) {
    const prevChainId = this.chainId();
    const prevWrongChain = this.isWrongChain();
    
    this.chainId.set(chainId);

    const supportedChain = this.supportedChains.find(c => Number(c.id) === chainId);
    const isSupported = !!supportedChain;
    this.isWrongChain.set(!isSupported);

    if (isSupported) {
      this.networkName.set(supportedChain.name);
      const symbol = (supportedChain as any).nativeCurrency?.symbol || 'ETH';
      this.chainSymbol.set(symbol);

      this.showWrongChainModal.set(false);
      if (prevWrongChain) {
        try {
          this.modal?.close();
        } catch (e) {
        }
      }
      if (showToastAlert && prevChainId && prevChainId !== chainId) {
        const switchMsg = `${this.lang.translate('toast.network_switched')} ${supportedChain.name}!`;
        this.toastService.showToast(switchMsg, 'success');
      }

      const isTestnet = !!supportedChain.testnet || supportedChain.name.toLowerCase().includes('sepolia') || supportedChain.name.toLowerCase().includes('testnet');
      if (isTestnet) {
        this.txSpeed.set('fast');
      } else {
        this.txSpeed.set('default');
      }
    } else {
      const popular = POPULAR_CHAINS.find(c => Number(c.chainId) === chainId);
      this.networkName.set(popular ? popular.name : this.lang.translate('web3.unsupported_network'));
      this.chainSymbol.set('ETH');
      
      if (this.isConnected()) {
        this.showWrongChainModal.set(true);
        if (showToastAlert && (!prevWrongChain || prevChainId !== chainId)) {
          this.toastService.showToast(this.lang.translate('toast.network_not_supported'), 'error');
        }
      } else {
        this.showWrongChainModal.set(false);
      }

      const isTestnet = popular 
        ? popular.name.toLowerCase().includes('sepolia') || popular.name.toLowerCase().includes('testnet')
        : (chainId === 421614 || chainId === 97 || chainId === 11155111);
      if (isTestnet) {
        this.txSpeed.set('fast');
      } else {
        this.txSpeed.set('default');
      }
    }
  }

  public async updateBalanceAndNetwork() {
    try {
      if (!this.modal) return;
      const walletProvider = this.modal.getWalletProvider();
      const currentAddress = this.address();

      if (walletProvider && currentAddress) {
        const { BrowserProvider, formatEther } = await import('ethers');
        const ethersProvider = new BrowserProvider(walletProvider as any);
        const balanceVal = await ethersProvider.getBalance(currentAddress);
        const formattedBalance = formatEther(balanceVal);
        this.balance.set(parseFloat(formattedBalance).toFixed(4));

        const network = await ethersProvider.getNetwork();
        const id = Number(network.chainId);
        this.checkAndUpdateNetworkState(id, false);
      }
    } catch (error) {
      console.error('Failed to update balance/network:', error);
    }
  }

  public async connect() {
    try {
      if (!this.modal) {
        this.toastService.showToast(this.lang.translate('toast.web3_disabled'), 'warning');
        return;
      }
      await this.modal.open();
    } catch (error) {
      console.error('Failed to connect wallet:', error);
    }
  }

  public async openNetworkModal() {
    try {
      if (!this.modal) return;
      await this.modal.open({ view: 'Networks' });
    } catch (error) {
      console.error('Failed to open network modal:', error);
    }
  }

  public async openAccountModal() {
    try {
      if (!this.modal) return;
      await this.modal.open({ view: 'Account' });
    } catch (error) {
      console.error('Failed to open account modal:', error);
    }
  }

  public async disconnect() {
    try {
      if (!this.modal) return;
      await this.modal.disconnect();
    } catch (error) {
      console.error('Failed to disconnect wallet:', error);
    }
  }

  public async switchNetwork(chainId: number) {
    try {
      if (!this.modal) return;
      const network = this.supportedChains.find(chain => Number(chain.id) === chainId);
      if (network) {
        await this.modal.switchNetwork(network as any);
      } else {
        console.warn(`[Web3] Network with chainId ${chainId} cannot be selected.`);
      }
    } catch (error) {
      console.error(`Failed to switch to network ${chainId}:`, error);
    }
  }

  public async getSigner() {
    if (!this.modal) {
      throw new Error(this.lang.translate('toast.web3_disabled'));
    }
    const walletProvider = this.modal.getWalletProvider();
    if (!walletProvider) {
      throw new Error(this.lang.translate('web3.wallet_not_connected'));
    }
    const { BrowserProvider } = await import('ethers');
    const ethersProvider = new BrowserProvider(walletProvider as any);

    const currentAddress = this.address();
    const originalSend = ethersProvider.send.bind(ethersProvider);
    ethersProvider.send = async (method: string, params: any[]) => {
      if (method === 'eth_requestAccounts' || method === 'eth_accounts') {
        if (currentAddress) {
          return [currentAddress];
        }
      }
      return await originalSend(method, params);
    };

    return await ethersProvider.getSigner();
  }

  public async getProvider() {
    if (!this.modal) {
      throw new Error(this.lang.translate('toast.web3_disabled'));
    }
    const walletProvider = this.modal.getWalletProvider();
    if (!walletProvider) {
      throw new Error(this.lang.translate('web3.wallet_not_connected'));
    }
    const { BrowserProvider } = await import('ethers');
    return new BrowserProvider(walletProvider as any);
  }

  public async getGasOverrides(signer?: any): Promise<any> {
    const overrides: any = {};
    try {
      let currentSigner = signer;
      if (!currentSigner) {
        currentSigner = await this.getSigner();
      }
      const provider = currentSigner.provider;
      if (!provider) return overrides;
      
      const feeData = await provider.getFeeData();
      const speed = this.txSpeed();
      if (speed !== 'default') {
        const multiplier = speed === 'fast' ? 1.5 : this.gasMultiplier();
        if (feeData.maxFeePerGas) {
          overrides.maxFeePerGas = (feeData.maxFeePerGas * BigInt(Math.round(multiplier * 100))) / 100n;
        }
        if (feeData.maxPriorityFeePerGas) {
          overrides.maxPriorityFeePerGas = (feeData.maxPriorityFeePerGas * BigInt(Math.round(multiplier * 100))) / 100n;
        }
      }
    } catch (err) {
      console.warn('[Web3] Failed to retrieve fee data for gas overrides:', err);
    }
    return overrides;
  }
}
