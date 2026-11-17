<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpsApiTest extends TestCase
{
    public function test_missing_or_wrong_token_is_404(): void
    {
        $this->get('/ops/health')->assertNotFound();
        $this->get('/ops/health?k=wrong')->assertNotFound();
    }

    public function test_health_answers_with_token(): void
    {
        $this->withToken(config('ops.token'))->get('/ops/health')->assertOk()->assertSee('php:');
    }

    public function test_logs_answer_with_query_token(): void
    {
        $this->get('/ops/logs?k='.config('ops.token'))->assertOk();
    }

    public function test_expired_token_is_404(): void
    {
        config(['ops.expires' => '2020-01-01']);

        $this->withToken(config('ops.token'))->get('/ops/health')->assertNotFound();
    }
}
