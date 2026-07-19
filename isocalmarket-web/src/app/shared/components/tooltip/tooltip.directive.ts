import { Directive, Input, ElementRef, HostListener, Renderer2, OnDestroy } from '@angular/core';

@Directive({
  selector: '[appTooltip]',
  standalone: true
})
export class TooltipDirective implements OnDestroy {
  @Input('appTooltip') text: string = '';
  @Input() tooltipPlacement: 'top' | 'bottom' | 'left' | 'right' = 'top';

  private tooltipEl: HTMLElement | null = null;
  private removeListeners: (() => void)[] = [];

  constructor(
    private el: ElementRef,
    private renderer: Renderer2
  ) {}

  @HostListener('mouseenter')
  @HostListener('focusin')
  onMouseEnter(): void {
    if (!this.text) return;
    this.createTooltip();
  }

  @HostListener('mouseleave')
  @HostListener('focusout')
  @HostListener('click')
  onMouseLeave(): void {
    this.destroyTooltip();
  }

  ngOnDestroy(): void {
    this.destroyTooltip();
  }

  private createTooltip(): void {
    if (this.tooltipEl) return;
    this.tooltipEl = this.renderer.createElement('div');
    this.renderer.appendChild(this.tooltipEl, this.renderer.createText(this.text));

    this.renderer.addClass(this.tooltipEl, 'app-tooltip');
    this.renderer.appendChild(document.body, this.tooltipEl);

    this.updatePosition();

    requestAnimationFrame(() => {
      if (this.tooltipEl) {
        this.renderer.addClass(this.tooltipEl, 'app-tooltip-show');
        this.updatePosition(true);
      }
    });

    const scrollListener = this.renderer.listen('window', 'scroll', () => this.destroyTooltip());
    const resizeListener = this.renderer.listen('window', 'resize', () => this.destroyTooltip());
    this.removeListeners.push(scrollListener, resizeListener);
  }

  private updatePosition(active = false): void {
    if (!this.tooltipEl) return;

    const hostRect = this.el.nativeElement.getBoundingClientRect();
    let top = 0;
    let left = 0;
    let transform = '';

    switch (this.tooltipPlacement) {
      case 'top':
        top = hostRect.top;
        left = hostRect.left + hostRect.width / 2;
        transform = `translate(-50%, -100%) translateY(-6px) ${active ? 'scale(1)' : 'scale(0.95)'}`;
        break;
      case 'bottom':
        top = hostRect.bottom;
        left = hostRect.left + hostRect.width / 2;
        transform = `translate(-50%, 0) translateY(6px) ${active ? 'scale(1)' : 'scale(0.95)'}`;
        break;
      case 'left':
        top = hostRect.top + hostRect.height / 2;
        left = hostRect.left;
        transform = `translate(-100%, -50%) translateX(-6px) ${active ? 'scale(1)' : 'scale(0.95)'}`;
        break;
      case 'right':
        top = hostRect.top + hostRect.height / 2;
        left = hostRect.right;
        transform = `translate(0, -50%) translateX(6px) ${active ? 'scale(1)' : 'scale(0.95)'}`;
        break;
    }

    this.renderer.setStyle(this.tooltipEl, 'top', `${top}px`);
    this.renderer.setStyle(this.tooltipEl, 'left', `${left}px`);
    this.renderer.setStyle(this.tooltipEl, 'transform', transform);
  }

  private destroyTooltip(): void {
    if (!this.tooltipEl) return;

    this.removeListeners.forEach(fn => fn());
    this.removeListeners = [];

    const el = this.tooltipEl;
    this.tooltipEl = null;
    this.renderer.removeClass(el, 'app-tooltip-show');
    this.renderer.setStyle(el, 'transform', el.style.transform + ' scale(0.95)');

    setTimeout(() => {
      if (el.parentNode) {
        this.renderer.removeChild(document.body, el);
      }
    }, 150);
  }
}
