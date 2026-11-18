<?php

namespace Tests\Feature;

use Tests\TestCase;

// No database: an unsigned call must be refused before anything is looked up.
class InstagramCallbacksTest extends TestCase
{
    public function test_unsigned_calls_are_refused(): void
    {
        config(['services.instagram.client_secret' => 'secret']);

        $this->post('/instagram/deauthorize', ['signed_request' => 'bad.bad'])->assertStatus(400);
        $this->post('/instagram/data-deletion')->assertStatus(400);
    }

    public function test_privacy_page_is_public(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Removing your data');
    }
}
