import {
  Injectable,
  ApplicationRef,
  ComponentRef,
  createComponent,
  Type,
  Injector} from '@angular/core';
import { ModalRef, MODAL_DATA, ModalConfig, ConfirmConfig } from './modal-ref';
import { ModalWrapperComponent } from '@shared/components/modal/modal-wrapper.component';
import { ConfirmModalComponent } from '@shared/components/confirm-modal/confirm-modal.component';
import { LanguageService } from './language.service';

@Injectable({
  providedIn: 'root'})
export class ModalService {
  constructor(
    private appRef: ApplicationRef,
    private injector: Injector,
    private languageService: LanguageService
  ) {}

  open<T, D = any, R = any>(
    component: Type<T>,
    config: ModalConfig<D>
  ): ModalRef<R> {
    let componentRef: ComponentRef<ModalWrapperComponent> | null = null;

    const modalRef = new ModalRef<R>((result?: R) => {
      if (componentRef) {
        this.appRef.detachView(componentRef.hostView);
        componentRef.destroy();
      }
    });
    const childInjector = Injector.create({
      providers: [
        { provide: ModalRef, useValue: modalRef },
        { provide: MODAL_DATA, useValue: config.data }
      ],
      parent: this.injector
    });
    componentRef = createComponent(ModalWrapperComponent, {
      environmentInjector: this.appRef.injector,
      elementInjector: childInjector
    });

    componentRef.instance.title = config.title;
    componentRef.instance.size = config.size || 'md';
    componentRef.instance.closeOnBackdropClick = config.closeOnBackdropClick ?? true;
    componentRef.instance.showHeader = config.showHeader ?? true;
    componentRef.instance.childComponent = component;
    componentRef.instance.childInjector = childInjector;

    this.appRef.attachView(componentRef.hostView);

    const domElem = (componentRef.hostView as any).rootNodes[0] as HTMLElement;
    document.body.appendChild(domElem);

    return modalRef;
  }

  confirm(config: ConfirmConfig): ModalRef<boolean> {
    return this.open(ConfirmModalComponent, {
      title: config.title || this.languageService.translate('modal.confirm_title'),
      size: config.size || 'sm',
      closeOnBackdropClick: true,
      showHeader: false,
      data: config
    });
  }
}
