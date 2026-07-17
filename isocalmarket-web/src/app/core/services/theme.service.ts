import { Injectable, signal } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ThemeService {
  public readonly themeMode = signal<'light' | 'dark' | 'auto'>('auto');
  
  public readonly isDarkMode = signal<boolean>(false);
  
  // Đối tượng lắng nghe thay đổi theme hệ thống
  private mediaQueryList: MediaQueryList | null = null;

  constructor() {
    this.initTheme();
  }

  private initTheme() {
    if (typeof window === 'undefined') return;
    
    const savedTheme = localStorage.getItem('theme_mode') as 'light' | 'dark' | 'auto' | null;
    this.mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Lắng nghe sự thay đổi theme của hệ thống
    this.mediaQueryList.addEventListener('change', this.handleSystemThemeChange);

    if (savedTheme === 'light') {
      this.setThemeMode('light');
    } else if (savedTheme === 'dark') {
      this.setThemeMode('dark');
    } else {
      this.setThemeMode('auto');
    }
  }
  public applyDarkClass(dark: boolean) {
    this.isDarkMode.set(dark);
    if (typeof document !== 'undefined') {
      const htmlEl = document.documentElement;
      const bodyEl = document.body;
      
      if (dark) {
        htmlEl.classList.add('dark');
        bodyEl.classList.add('dark');
      } else {
        htmlEl.classList.remove('dark');
        bodyEl.classList.remove('dark');
      }
    }
  }
  public setThemeMode(mode: 'light' | 'dark' | 'auto') {
    this.themeMode.set(mode);
    if (typeof window !== 'undefined') {
      localStorage.setItem('theme_mode', mode);
    }

    if (mode === 'auto') {
      if (this.mediaQueryList) {
        this.applyDarkClass(this.mediaQueryList.matches);
      }
    } else {
      this.applyDarkClass(mode === 'dark');
    }
  }

  // Chuyển đổi qua lại giữa 3 chế độ: light -> dark -> auto
  public toggleTheme() {
    const current = this.themeMode();
    if (current === 'light') {
      this.setThemeMode('dark');
    } else if (current === 'dark') {
      this.setThemeMode('auto');
    } else {
      this.setThemeMode('light');
    }
  }
  private handleSystemThemeChange = (e: MediaQueryListEvent) => {
    if (this.themeMode() === 'auto') {
      this.applyDarkClass(e.matches);
    }
  };
}
