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
        $service = new GoogleTranslateService();
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

        $service = new GoogleTranslateService();
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
            'translate.googleapis.com/*' => Http::response(null, 500),
        ]);

        $service = new GoogleTranslateService();
        $result = $service->translate('Sample product text', 'vi');

        $this->assertEquals('Sample product text', $result);
    }
}
