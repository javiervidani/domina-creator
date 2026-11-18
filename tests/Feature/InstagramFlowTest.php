<?php

namespace Tests\Feature;

use App\Platforms\Instagram\InstagramProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Tests\TestCase;

// The whole Instagram sign-in against a fake Instagram, so it can be checked without a Meta app.
class InstagramFlowTest extends TestCase
{
    public function test_code_is_exchanged_for_a_long_lived_token_and_the_user(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => [['access_token' => 'short', 'user_id' => '42']]])), // code -> short token
            new Response(200, [], json_encode(['access_token' => 'long', 'expires_in' => 5184000])),           // short -> 60 days
            new Response(200, [], json_encode(['user_id' => '42', 'username' => 'creator'])),                 // who is it
        ]);

        $provider = new InstagramProvider(Request::create('/cb', 'GET', ['code' => 'abc']), 'id', 'secret', 'https://x.test/cb');
        $provider->setHttpClient(new Client(['handler' => HandlerStack::create($mock)]));

        $user = $provider->stateless()->user();

        $this->assertSame('42', (string) $user->getId());
        $this->assertSame('creator', $user->getNickname());
        $this->assertSame('long', $user->token);
        $this->assertSame(5184000, (int) $user->expiresIn);
    }

    public function test_authorize_url_carries_the_app_and_the_redirect(): void
    {
        $url = (new InstagramProvider(Request::create('/'), 'app-id', 'secret', 'https://x.test/cb'))->stateless()->redirect()->getTargetUrl();

        $this->assertStringContainsString('client_id=app-id', $url);
        $this->assertStringContainsString('redirect_uri='.urlencode('https://x.test/cb'), $url);
        $this->assertStringContainsString('instagram_business_basic', $url);
    }
}
