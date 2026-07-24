<?php

namespace App\Application\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleTranslateService
{
    private const BATCH_CONCURRENCY = 10;

    private const CACHE_DAYS = 30;

    private array $memoryCache = [];

    public function clearMemoryCache(): void
    {
        $this->memoryCache = [];
    }

    /**
     * Dịch văn bản từ ngôn ngữ tự động (sl=auto) sang ngôn ngữ đích (tl=$targetLang).
     */
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
            return $cleanText;
        }

        Cache::put($cacheKey, $result, now()->addDays(self::CACHE_DAYS));
        $this->memoryCache[$memKey] = $result;

        return $result;
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
        $uniqueTexts = [];

        foreach ($texts as $text) {
            if (! is_string($text)) {
                continue;
            }

            $cleanText = trim($text);
            if ($cleanText !== '') {
                $uniqueTexts[$cleanText] = $cleanText;
            }
        }

        $results = [];
        foreach ($uniqueTexts as $cleanText) {
            $results[$cleanText] = $this->translate($cleanText, $targetLang) ?? $cleanText;
        }

        return $results;
    }

    private function fetchFromGoogle(string $text, string $targetLang): ?string
    {
        try {
            $response = Http::timeout(5)
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
        return 'gtrans_v2_'.md5($text.'_'.$targetLang);
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
