import { Directive, Input, ElementRef, HostListener, Renderer2, NgZone, OnInit } from '@angular/core';

@Directive({
  selector: '[appRipple]',
  standalone: true,
})
export class RippleDirective implements OnInit {
  @Input('appRippleColor') color: string = '';
  @Input('appRippleCentered') centered: boolean = false;
  @Input('appRippleDisabled') disabled: boolean = false;
  @Input('appRippleUnbounded') unbounded: boolean = false;
  @Input('appRippleRadius') radius: number = 0;
  @Input('appRippleDuration') duration: number = 650;
  @Input('appRippleOpacity') opacity: number | null = 0.45;

  private lastTouchTimestamp = 0;

  constructor(
    private el: ElementRef<HTMLElement>,
    private renderer: Renderer2,
    private ngZone: NgZone,
  ) {}

  ngOnInit(): void {
    const nativeEl = this.el.nativeElement;
    const isAnchorLink = nativeEl.tagName.toLowerCase() === 'a' && nativeEl.hasAttribute('href');
    if (!isAnchorLink) {
      this.renderer.setAttribute(nativeEl, 'draggable', 'false');
    }
    if (!this.unbounded) {
      const computedStyle = getComputedStyle(nativeEl);
      if (computedStyle.position === 'static') {
        this.renderer.setStyle(nativeEl, 'position', 'relative');
      }
      this.renderer.setStyle(nativeEl, 'overflow', 'hidden');
      this.renderer.setStyle(nativeEl, 'isolation', 'isolate');
      this.renderer.setStyle(nativeEl, 'contain', 'paint');
    }
  }

  @HostListener('mousedown', ['$event'])
  onMouseDown(event: MouseEvent): void {
    if (this.disabled || event.button !== 0) return;
    if (Date.now() - this.lastTouchTimestamp < 800) return;
    this.createRipple(event);
  }

  @HostListener('touchstart', ['$event'])
  onTouchStart(event: TouchEvent): void {
    if (this.disabled) return;
    this.lastTouchTimestamp = Date.now();
    this.createRipple(event);
  }

  private createRipple(event: MouseEvent | TouchEvent): void {
    const container = this.el.nativeElement;

    const computedStyle = getComputedStyle(container);
    if (computedStyle.position === 'static') {
      this.renderer.setStyle(container, 'position', 'relative');
    }
    if (this.unbounded) {
      this.renderer.setStyle(container, 'overflow', 'visible');
    } else {
      this.renderer.setStyle(container, 'overflow', 'hidden');
      this.renderer.setStyle(container, 'isolation', 'isolate');
      this.renderer.setStyle(container, 'contain', 'paint');
    }

    const ripple = this.renderer.createElement('span');
    this.renderer.addClass(ripple, 'app-ripple-element');

    const rect = container.getBoundingClientRect();
    const isCentered = this.centered;

    let x = 0;
    let y = 0;
    let size = 0;

    if (isCentered) {
      x = rect.width / 2;
      y = rect.height / 2;
    } else {
      const clientX = event instanceof MouseEvent ? event.clientX : event.touches[0].clientX;
      const clientY = event instanceof MouseEvent ? event.clientY : event.touches[0].clientY;
      x = clientX - rect.left;
      y = clientY - rect.top;
    }

    if (this.radius > 0) {
      size = this.radius * 2;
    } else {
      if (this.unbounded) {
        size = Math.sqrt(rect.width * rect.width + rect.height * rect.height);
      } else {
        const maxDistX = Math.max(x, rect.width - x);
        const maxDistY = Math.max(y, rect.height - y);
        size = Math.sqrt(maxDistX * maxDistX + maxDistY * maxDistY) * 2;
      }
    }

    this.renderer.setStyle(ripple, 'width', `${size}px`);
    this.renderer.setStyle(ripple, 'height', `${size}px`);
    this.renderer.setStyle(ripple, 'left', `${x - size / 2}px`);
    this.renderer.setStyle(ripple, 'top', `${y - size / 2}px`);

    if (this.color) {
      this.renderer.setStyle(ripple, 'background-color', this.color);
    }

    let finalOpacity = 0.45;
    if (this.opacity !== null) {
      finalOpacity = this.opacity;
    }
    this.renderer.setStyle(ripple, '--ripple-target-opacity', finalOpacity.toString());
    this.renderer.setStyle(ripple, '--ripple-duration', `${this.duration}ms`);
    this.renderer.setStyle(ripple, 'animation-duration', `${this.duration}ms`);

    let targetParent: HTMLElement = container;
    let rippleHolder: HTMLElement | null = null;
    if (!this.unbounded) {
      const existing = container.querySelector(':scope > .app-ripple-container') as HTMLElement | null;
      if (existing) {
        rippleHolder = existing;
      } else {
        rippleHolder = this.renderer.createElement('div') as HTMLElement;
        this.renderer.addClass(rippleHolder, 'app-ripple-container');
        this.renderer.appendChild(container, rippleHolder);
      }
      targetParent = rippleHolder;
    }

    this.renderer.appendChild(targetParent, ripple);

    this.ngZone.runOutsideAngular(() => {
      setTimeout(() => {
        ripple.remove();
        if (rippleHolder && rippleHolder.childNodes.length === 0) {
          rippleHolder.remove();
        }
      }, this.duration);
    });
  }
}
