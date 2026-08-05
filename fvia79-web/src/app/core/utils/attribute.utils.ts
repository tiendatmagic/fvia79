export interface ProductAttribute {
  label: string;
  value: string;
}

export function formatAttributes(raw: any, includeRaw = false): ProductAttribute[] {
  if (!raw) return [];

  let parsed = raw;
  if (typeof raw === 'string') {
    try {
      parsed = JSON.parse(raw);
    } catch {
      return [];
    }
  }

  const result: ProductAttribute[] = [];

  if (Array.isArray(parsed)) {
    for (const item of parsed) {
      if (item && typeof item === 'object') {
        const label = String(item.label ?? item.key ?? '').trim();
        const value = String(item.value ?? item.val ?? '').trim();
        if (label) {
          if (!includeRaw && (label === 'raw_name' || label === 'raw_desc')) {
            continue;
          }
          result.push({ label, value });
        }
      }
    }
  } else if (parsed && typeof parsed === 'object') {
    for (const [key, val] of Object.entries(parsed)) {
      if (!includeRaw && (key === 'raw_name' || key === 'raw_desc')) continue;

      if (val && typeof val === 'object' && ('label' in val || 'value' in val)) {
        const label = String((val as any).label ?? key).trim();
        const value = String((val as any).value ?? '').trim();
        if (label) {
          if (!includeRaw && (label === 'raw_name' || label === 'raw_desc')) {
            continue;
          }
          result.push({ label, value });
        }
      } else if (val !== null && val !== undefined) {
        const label = String(key).trim();
        const value = String(val).trim();
        if (label) {
          if (!includeRaw && (label === 'raw_name' || label === 'raw_desc')) {
            continue;
          }
          result.push({ label, value });
        }
      }
    }
  }

  return result;
}
