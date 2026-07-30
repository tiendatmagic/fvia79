import { describe, expect, it } from 'vitest';
import { isDefinitiveAuthFailure } from './auth-error.utils';

describe('isDefinitiveAuthFailure', () => {
  it.each([401, 403])('treats HTTP %s as a rejected session', (status) => {
    expect(isDefinitiveAuthFailure({ status })).toBe(true);
  });

  it.each([0, 408, 429, 500, 502, 503, 504])(
    'does not treat transient HTTP %s as a rejected session',
    (status) => {
      expect(isDefinitiveAuthFailure({ status })).toBe(false);
    }
  );
});
