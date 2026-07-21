# ADS7979 Supplier Integration

## Goal
Import and continuously synchronize ADS7979 catalogs into the local USD-priced store through an extensible supplier layer.

## Tasks
- [x] Add normalized supplier, external category, and external product fields with indexes and encrypted credentials. -> Verify migrations run and roll back.
- [x] Implement the ADS7979 V2 client, price conversion/adjustment rules, and idempotent catalog synchronization. -> Verify 12 categories and 115 products can be mapped without duplicates.
- [x] Expose admin supplier configuration, status, and manual-sync REST endpoints. -> Verify admin authorization, validation, and secret masking.
- [x] Add `suppliers:sync` and a due-aware 10-minute scheduler supporting 10/30-minute intervals. -> Verify command and scheduled task registration.
- [x] Show supplier provenance/stock in products and add an admin settings panel for API key, markup, rate, interval, auto-sync, and manual import. -> Verify Angular build and responsive states.
- [x] Add unit/feature tests using mocked ADS7979 responses, including fixed/percent adjustments and repeat sync. -> Verify backend and frontend test/build commands.
- [x] Run migrations, targeted tests, full builds, and repository validation. -> Verify no secret is committed and all checks pass.

## Done When
- [x] ADS7979 products are stored once, priced in USD after configured adjustment, carry source metadata, expose remote stock, and can be refreshed manually or on schedule.
- [x] The integration can support another supplier by adding a connector without changing product synchronization rules.

## Notes
- ADS7979 prices are VND; conversion reuses the cached CoinGecko USDT/VND rate from the deposit flow.
- Import/sync is read-only against ADS7979. Upstream purchasing is intentionally outside this catalog-sync scope.
