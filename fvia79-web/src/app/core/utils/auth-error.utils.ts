export function isDefinitiveAuthFailure(error: unknown): boolean {
  const status = Number((error as { status?: unknown } | null)?.status);

  return status === 401 || status === 403;
}
