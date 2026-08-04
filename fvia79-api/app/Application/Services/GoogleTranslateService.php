<?php

namespace App\Application\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleTranslateService
{
    private const BATCH_CONCURRENCY = 30;

    private const CACHE_DAYS = 30;

    private array $memoryCache = [];

    public function clearMemoryCache(): void
    {
        $this->memoryCache = [];
    }

    public function translate(?string $text, string $targetLang = 'vi'): ?string
    {
        if ($text === null) {
            return null;
        }

        $cleanText = trim($text);
        if ($cleanText === '') {
            return '';
        }
        $targetLang = strtolower(trim($targetLang));
        if ($targetLang === '') {
            $targetLang = 'vi';
        }

        $memKey = $this->memoryKey($cleanText, $targetLang);
        if (array_key_exists($memKey, $this->memoryCache)) {
            return $this->memoryCache[$memKey];
        }

        $cacheKey = $this->cacheKey($cleanText, $targetLang);
        $cached = Cache::get($cacheKey);
        if (is_string($cached)) {
            $this->memoryCache[$memKey] = $cached;

            return $cached;
        }

        $result = $this->fetchFromGoogle($cleanText, $targetLang);
        if ($result === null) {
            return $targetLang === 'en' ? $this->applyMmoDictionaryEn($cleanText) : $cleanText;
        }

        if ($targetLang === 'en') {
            $result = $this->applyMmoDictionaryEn($result);
        }

        Cache::put($cacheKey, $result, now()->addDays(self::CACHE_DAYS));
        $this->memoryCache[$memKey] = $result;

        return $result;
    }

    public function applyMmoDictionaryEn(string $text): string
    {
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;
        }

        $replacements = [
            '/\bSiêu\s+Cổ\b/ui' => 'Super Aged',
            '/\bCổ\b/ui' => 'Aged',
            '/\bNgoại\b/ui' => 'Foreign',
            '/\bNgoai\b/ui' => 'Foreign',
            '/\bKháng\b/ui' => 'Resistant',
            '/\bKhang\b/ui' => 'Resistant',
            '/\bXMDT\b/ui' => 'Identity Verified',
            '/\bXác\s+Minh\s+Danh\s+Tính\b/ui' => 'Identity Verified',
            '/\bChưa\s+Gắn\b/ui' => 'Unlinked',
            '/\bChống\s+Back\b/ui' => 'Anti-Back',
            '/\bViệt\b/ui' => 'Vietnamese',
            '/\bBè\b/ui' => 'Friends',
            '/\bCó\s+TKQC\b/ui' => 'With Ad Account',
            '/\bTKQC\b/ui' => 'Ad Account',
            '/\bCó\s+TK\b/ui' => 'With Account',
            '/\bCó\b/ui' => 'With',
            '/\bKhông\s+Tạo\s+Được\s+Tài\s+Khoản\b/ui' => 'Unable to Create Account',
            '/\bCầm\s+Đối\s+Tác\b/ui' => 'Partner Holding',
            '/\bDòng\s+2\b/ui' => 'Line 2',
            '/\bXác\s+Minh\s+Doanh\s+Nghiệp\b/ui' => 'Business Verified',
            '/\bXMDN\b/ui' => 'Business Verified',
            '/\bNgâm\b/ui' => 'Seasoned',
            '/\bTrống\b/ui' => 'Empty',
            '/\bTài\s+Khoản\b/ui' => 'Account',
            '/\bđã\s+paid\s+mồi\b/ui' => 'warmup paid',
            '/\bCo\s+(XMDT|Limit|Resistant|Die|Live|Friends|Friend|\d+K?)\b/ui' => 'Aged $1',
            '/\bStock\s+(Identity\s+Verified|Limit|Resistant|Die|Live|Friends|Friend|\d+K?)\b/ui' => 'Aged $1',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        $text = preg_replace('/\b(Via\s+[\w]+)\s+Co\b/ui', '$1 Aged', $text);
        $text = preg_replace('/\b(Foreign)\s+Co\b/ui', '$1 Aged', $text);

        return $text;
    }

    /**
     * Pre-translates unique texts in bounded concurrent batches.
     *
     * Failed translations are kept only in this service instance so catalog
     * persistence can safely fall back without retrying inside its transaction.
     * They are not stored in the persistent 30-day cache.
     *
     * @param  array<int, mixed>  $texts
     * @return array<string, string>
     */
    public function translateMany(array $texts, string $targetLang = 'vi'): array
    {
        $targetLang = $this->normalizeTargetLanguage($targetLang);
        $toFetch = [];
        $results = [];

        foreach ($texts as $text) {
            if (! is_string($text)) {
                continue;
            }

            $cleanText = trim($text);
            if ($cleanText === '') {
                continue;
            }

            $memKey = $this->memoryKey($cleanText, $targetLang);
            if (array_key_exists($memKey, $this->memoryCache)) {
                $results[$cleanText] = $this->memoryCache[$memKey];
                continue;
            }

            $cacheKey = $this->cacheKey($cleanText, $targetLang);
            $cached = Cache::get($cacheKey);
            if (is_string($cached)) {
                $this->memoryCache[$memKey] = $cached;
                $results[$cleanText] = $cached;
                continue;
            }

            $toFetch[$cleanText] = $cleanText;
        }

        if ($toFetch === []) {
            return $results;
        }

        $chunks = array_chunk(array_values($toFetch), self::BATCH_CONCURRENCY);
        foreach ($chunks as $chunk) {
            try {
                $responses = Http::pool(function ($pool) use ($chunk, $targetLang) {
                    $requests = [];
                    foreach ($chunk as $index => $text) {
                        $requests[] = $pool->as((string) $index)
                            ->withoutVerifying()
                            ->timeout(4)
                            ->withHeaders($this->headers())
                            ->get('https://translate.googleapis.com/translate_a/single', [
                                'client' => 'gtx',
                                'sl' => 'auto',
                                'tl' => $targetLang,
                                'dt' => 't',
                                'q' => $text,
                            ]);
                    }

                    return $requests;
                });

                foreach ($chunk as $index => $text) {
                    $response = $responses[(string) $index] ?? null;
                    $translated = null;
                    if ($response instanceof Response && $response->successful()) {
                        $translated = $this->parseResponse($response, $text);
                    }

                    if ($translated === null) {
                        $translated = $targetLang === 'en' ? $this->applyMmoDictionaryEn($text) : $text;
                    } elseif ($targetLang === 'en') {
                        $translated = $this->applyMmoDictionaryEn($translated);
                    }

                    $cacheKey = $this->cacheKey($text, $targetLang);
                    $memKey = $this->memoryKey($text, $targetLang);
                    Cache::put($cacheKey, $translated, now()->addDays(self::CACHE_DAYS));
                    $this->memoryCache[$memKey] = $translated;
                    $results[$text] = $translated;
                }
            } catch (Throwable $e) {
                Log::warning('GoogleTranslateService pool failed: '.$e->getMessage());
                foreach ($chunk as $text) {
                    $translated = $targetLang === 'en' ? $this->applyMmoDictionaryEn($text) : $text;
                    $results[$text] = $translated;
                }
            }
        }

        return $results;
    }

    private function fetchFromGoogle(string $text, string $targetLang): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(4)
                ->withHeaders($this->headers())
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $text,
                ]);

            return $this->parseResponse($response, $text);
        } catch (Throwable $e) {
            Log::warning('GoogleTranslateService failed to translate text: '.$e->getMessage());

            return null;
        }
    }

    private function parseResponse(Response $response, string $originalText): ?string
    {
        if (! $response->successful()) {
            Log::warning('GoogleTranslateService API returned error status: '.$response->status());

            return null;
        }

        $data = $response->json();
        if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
            return null;
        }

        $translatedSegments = [];
        foreach ($data[0] as $segment) {
            if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                $translatedSegments[] = $segment[0];
            }
        }

        if ($translatedSegments === []) {
            return null;
        }

        return implode('', $translatedSegments) ?: $originalText;
    }

    private function normalizeTargetLanguage(string $targetLang): string
    {
        $targetLang = strtolower(trim($targetLang));

        return $targetLang !== '' ? $targetLang : 'vi';
    }

    private function memoryKey(string $text, string $targetLang): string
    {
        return hash('sha256', $targetLang."\0".$text);
    }

    private function cacheKey(string $text, string $targetLang): string
    {
        // Versioned to avoid legacy entries that may have cached API failures.
        return 'gtrans_v3_'.md5($text.'_'.$targetLang);
    }

    private function requestKey(string $text, string $targetLang): string
    {
        return 'translation_'.$this->memoryKey($text, $targetLang);
    }

    private function headers(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        ];
    }
}
