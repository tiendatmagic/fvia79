const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

function decodeBase32(secret: string): Uint8Array {
  const normalized = secret.toUpperCase().replace(/[\s=-]/g, '');
  if (!normalized) {
    throw new Error('TOTP secret is empty.');
  }

  let bits = 0;
  let value = 0;
  const bytes: number[] = [];

  for (const character of normalized) {
    const index = BASE32_ALPHABET.indexOf(character);
    if (index === -1) {
      throw new Error('TOTP secret is not valid Base32.');
    }

    value = (value * 32) + index;
    bits += 5;

    while (bits >= 8) {
      bits -= 8;
      bytes.push(Math.floor(value / (2 ** bits)) & 0xff);
      value %= 2 ** bits;
    }
  }

  return new Uint8Array(bytes);
}

export async function generateTotp(secret: string, timestamp = Date.now()): Promise<string> {
  if (!globalThis.crypto?.subtle) {
    throw new Error('Web Crypto is unavailable.');
  }

  const counter = Math.floor(timestamp / 30_000);
  const counterBytes = new Uint8Array(8);
  let remaining = counter;

  for (let index = counterBytes.length - 1; index >= 0; index--) {
    counterBytes[index] = remaining % 256;
    remaining = Math.floor(remaining / 256);
  }

  const secretBytes = decodeBase32(secret);
  const secretBuffer = secretBytes.buffer.slice(
    secretBytes.byteOffset,
    secretBytes.byteOffset + secretBytes.byteLength
  ) as ArrayBuffer;
  const key = await globalThis.crypto.subtle.importKey(
    'raw',
    secretBuffer,
    { name: 'HMAC', hash: 'SHA-1' },
    false,
    ['sign']
  );
  const signature = new Uint8Array(
    await globalThis.crypto.subtle.sign('HMAC', key, counterBytes.buffer as ArrayBuffer)
  );
  const offset = signature[signature.length - 1] & 0x0f;
  const binaryCode = (
    ((signature[offset] & 0x7f) * 0x1000000) +
    (signature[offset + 1] * 0x10000) +
    (signature[offset + 2] * 0x100) +
    signature[offset + 3]
  );

  return String(binaryCode % 1_000_000).padStart(6, '0');
}
