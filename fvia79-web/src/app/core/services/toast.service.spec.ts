import { TestBed } from '@angular/core/testing';
import { beforeEach, describe, expect, it } from 'vitest';
import { LanguageService, TRANSLATIONS } from './language.service';
import { ToastService } from './toast.service';

describe('ToastService', () => {
  let languageService: LanguageService;
  let toastService: ToastService;

  beforeEach(() => {
    if (typeof localStorage !== 'undefined') {
      localStorage.clear();
    }
    TestBed.configureTestingModule({});
    languageService = TestBed.inject(LanguageService);
    toastService = TestBed.inject(ToastService);
  });

  it('keeps an unknown English API message in the English interface', () => {
    languageService.setLanguage('en');

    expect(toastService.resolveApiMessage('Unexpected backend message.'))
      .toBe('Unexpected backend message.');
  });

  it('uses the localized fallback for an unknown English API message in the Vietnamese interface', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage(
      'Unexpected backend message.',
      0,
      languageService.translate('toast.operation_failed')
    )).toBe('Không thể hoàn tất thao tác. Vui lòng thử lại.');
  });

  it('preserves a message that is already translated into Vietnamese', () => {
    languageService.setLanguage('vi');
    const message = languageService.translate('toast.order_failed');

    expect(toastService.resolveApiMessage(message)).toBe(message);
  });

  it('translates a static backend error without losing its meaning', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage(
      'A transaction ID/hash is required for manual approval.'
    )).toBe(languageService.translate('error.transaction_id_required'));
  });

  it('translates a dynamic stock error and keeps its backend parameters', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage(
      "Product 'Account A' does not have enough stock available (Requested: 5, Available: 2)."
    )).toBe(languageService.translate('error.insufficient_stock_detail', {
      product: 'Account A',
      requested: '5',
      available: '2'
    }));
  });

  it('translates a dynamic balance error and keeps both amounts', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage(
      'Insufficient balance to pay for the order (Required: 12.5000 USD, Current: 3.2500 USD).'
    )).toBe(languageService.translate('error.insufficient_balance_detail', {
      required: '12.5000',
      current: '3.2500'
    }));
  });

  it('automatically resolves an exact English dictionary value', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage(
      TRANSLATIONS.en['toast.product_created']
    )).toBe(languageService.translate('toast.product_created'));
  });

  it('uses a type-specific fallback when displaying an unknown API message', () => {
    languageService.setLanguage('vi');

    toastService.showToast('New backend success message.', 'success');

    expect(toastService.toasts()[0].message)
      .toBe(languageService.translate('toast.operation_success'));
  });

  it('translates Unauthorized and Forbidden variants into localized error messages', () => {
    languageService.setLanguage('vi');

    expect(toastService.resolveApiMessage('Unauthorized.'))
      .toBe(languageService.translate('error.unauthorized'));
    expect(toastService.resolveApiMessage('Unauthorized'))
      .toBe(languageService.translate('error.unauthorized'));
    expect(toastService.resolveApiMessage('Unauthenticated.'))
      .toBe(languageService.translate('error.unauthorized'));
    expect(toastService.resolveApiMessage('Unauthenticated'))
      .toBe(languageService.translate('error.unauthorized'));
    expect(toastService.resolveApiMessage('Forbidden.'))
      .toBe(languageService.translate('error.forbidden'));
    expect(toastService.resolveApiMessage('Forbidden'))
      .toBe(languageService.translate('error.forbidden'));
  });
});
