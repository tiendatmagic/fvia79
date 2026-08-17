<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckUidTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_uid_is_accessible_without_authentication(): void
    {
        Http::fake([
            'https://graph2.facebook.com/v3.3/4/picture*' => Http::response([
                'data' => [
                    'height' => 50,
                    'is_silhouette' => false,
                    'url' => 'https://platform-lookaside.fbsbx.com/platform/profilepic/?psid=4',
                    'width' => 50,
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/tools/check-uid', [
            'uids' => "4\n",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 1,
                    'live_count' => 1,
                ],
            ]);
    }

    public function test_check_uid_validates_empty_input(): void
    {
        $response = $this->postJson('/api/v1/tools/check-uid', [
            'uids' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_check_uid_returns_live_and_die_results(): void
    {
        Http::fake([
            'https://graph2.facebook.com/v3.3/4/picture*' => Http::response([
                'data' => [
                    'height' => 50,
                    'is_silhouette' => false,
                    'url' => 'https://platform-lookaside.fbsbx.com/platform/profilepic/?psid=4',
                    'width' => 50,
                ],
            ], 200),
            'https://graph2.facebook.com/v3.3/999999999999999999/picture*' => Http::response([
                'error' => [
                    'message' => 'Some error',
                ],
            ], 400),
        ]);

        $response = $this->postJson('/api/v1/tools/check-uid', [
            'uids' => "4|pass123\n999999999999999999|diepass",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 2,
                    'live_count' => 1,
                    'die_count' => 1,
                    'live_uids' => ['4|pass123'],
                    'die_uids' => ['999999999999999999|diepass'],
                ],
            ]);
    }
}