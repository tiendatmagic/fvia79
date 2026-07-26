<?php

namespace Tests\Unit\Services;

use App\Application\Services\GoogleTranslateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleTranslateServiceTest extends TestCase
{
    public function test_translate_returns_null_or_empty_when_text_is_empty(): void
    {
        $service = new GoogleTranslateService;
        $this->assertNull($service->translate(null));
        $this->assertSame('', $service->translate('   '));
    }

    public function test_translate_calls_google_api_and_caches_result(): void
    {
        Cache::flush();
        Http::fake([
            'translate.googleapis.com/*' => Http::response([
                [
                    ['Tài khoản lưu trữ không giới hạn', 'Unlimited Storage Account', null, null, 1],
                ],
            ], 200),
        ]);

        $service = new GoogleTranslateService;
        $result = $service->translate('Unlimited Storage Account', 'vi');

        $this->assertEquals('Tài khoản lưu trữ không giới hạn', $result);
        Http::assertSentCount(1);

        // Second call should come from cache, no new HTTP request
        $resultCached = $service->translate('Unlimited Storage Account', 'vi');
        $this->assertEquals('Tài khoản lưu trữ không giới hạn', $resultCached);
        Http::assertSentCount(1);
    }

    public function test_translate_returns_original_text_on_api_failure(): void
    {
        Cache::flush();
        Http::fake([
            'translate.googleapis.com/*' => Http::sequence()
                ->push(null, 500)
                ->push([
                    [
                        ['Translated sample', 'Sample product text', null, null, 1],
                    ],
                ], 200),
        ]);

        $service = new GoogleTranslateService;
        $result = $service->translate('Sample product text', 'vi');

        $this->assertEquals('Sample product text', $result);

        // A transient failure must not poison the persistent cache for 30 days.
        $retried = (new GoogleTranslateService)->translate('Sample product text', 'vi');
        $this->assertEquals('Translated sample', $retried);
    }

    public function test_translate_many_deduplicates_requests_and_reuses_persistent_cache(): void
    {
        Cache::flush();
        Http::fake([
            'translate.googleapis.com/*' => Http::response([
                [
                    ['Translated', 'Original', null, null, 1],
                ],
            ], 200),
        ]);

        $service = new GoogleTranslateService;
        $service->translateMany(['First text', 'Second text', 'First text'], 'vi');

        Http::assertSentCount(2);

        (new GoogleTranslateService)->translateMany(['First text', 'Second text'], 'vi');
        Http::assertSentCount(2);
    }

    public function test_apply_mmo_dictionary_en_replaces_untranslated_vietnamese_mmo_terms(): void
    {
        $service = new GoogleTranslateService;
        $this->assertEquals('Via Indonesia Aged 3-5K Friend', $service->applyMmoDictionaryEn('Via Indonesia Cổ 3-5K Friend'));
        $this->assertEquals('Via Foreign Live Ads (2FA)', $service->applyMmoDictionaryEn('Via Ngoại Live Ads (2FA)'));
        $this->assertEquals('Via Philippines Aged 3K-5K Friend', $service->applyMmoDictionaryEn('Via Philippines Cổ 3K-5K Friend'));
    }
}
