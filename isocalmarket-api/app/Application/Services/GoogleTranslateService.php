<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleTranslateService
{
    private static array $memoryCache = [];

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

        $memKey = $cleanText . '___' . $targetLang;
        if (isset(self::$memoryCache[$memKey])) {
            return self::$memoryCache[$memKey];
        }

        $cacheKey = 'gtrans_' . md5($cleanText . '_' . $targetLang);

        $result = Cache::remember($cacheKey, now()->addDays(30), function () use ($cleanText, $targetLang): string {
            return $this->fetchFromGoogle($cleanText, $targetLang);
        });

        self::$memoryCache[$memKey] = $result;

        return $result;
    }

    private function fetchFromGoogle(string $text, string $targetLang): string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $text,
                ]);

            if (! $response->successful()) {
                Log::warning('GoogleTranslateService API returned error status: ' . $response->status());

                return $text;
            }

            $data = $response->json();

            if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
                return $text;
            }

            $translatedSegments = [];
            foreach ($data[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $translatedSegments[] = $segment[0];
                }
            }

            if (empty($translatedSegments)) {
                return $text;
            }

            return implode('', $translatedSegments);
        } catch (Throwable $e) {
            Log::warning('GoogleTranslateService failed to translate text: ' . $e->getMessage());

            return $text;
        }
    }
}
