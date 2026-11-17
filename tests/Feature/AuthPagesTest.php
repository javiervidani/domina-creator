<?php

namespace Tests\Feature;

use Tests\TestCase;

// No database needed: these run on sqlite too.
class AuthPagesTest extends TestCase
{
    public function test_guest_pages_render(): void
    {
        foreach (['login', 'register', 'forgot-password'] as $path) {
            $this->get('/'.$path)->assertOk()->assertSee('bootstrap.min.css');
        }
    }

    public function test_library_sends_guests_to_sign_in(): void
    {
        $this->get('/library')->assertRedirect('/login');
    }
}
