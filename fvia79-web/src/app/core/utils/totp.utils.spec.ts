import { describe, expect, it } from 'vitest';
import { generateTotp } from './totp.utils';

describe('generateTotp', () => {
  it('generates the RFC 6238 SHA-1 test token', async () => {
    const token = await generateTotp('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 59_000);

    expect(token).toBe('287082');
  });

  it('rejects invalid Base32 secrets', async () => {
    await expect(generateTotp('INVALID!')).rejects.toThrow('Base32');
  });
});
