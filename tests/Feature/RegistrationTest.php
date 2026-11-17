<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\CreatorMember;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only, like TenancyTest. Rows are removed afterwards; the creator role and schema stay (cheap, reusable).
class RegistrationTest extends TestCase
{
    private string $email;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        $this->email = 'reg'.uniqid().'@example.test';
    }

    protected function tearDown(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $user = User::where('email', $this->email)->first();
            if ($user) {
                $creatorId = CreatorMember::where('user_id', $user->id)->value('creator_id');
                Tenant::where('creator_id', $creatorId)->delete();
                CreatorMember::where('user_id', $user->id)->delete();
                Creator::whereKey($creatorId)->delete();
                $user->delete();
            }
        }
        parent::tearDown();
    }

    private function register(): void
    {
        $this->post('/register', [
            'name' => 'Test Creator', 'email' => $this->email,
            'password' => 'a-long-password', 'password_confirmation' => 'a-long-password',
        ]);
    }

    public function test_registration_ends_on_the_library_in_its_own_schema(): void
    {
        $this->register();

        $user = User::where('email', $this->email)->firstOrFail();
        $creatorId = CreatorMember::where('user_id', $user->id)->value('creator_id');

        $this->get('/library')->assertOk()->assertSee('creator_'.$creatorId);
    }

    public function test_sign_out_and_sign_in_again(): void
    {
        $this->register();
        $this->post('/logout')->assertRedirect('/login');
        $this->get('/library')->assertRedirect('/login');

        $this->post('/login', ['email' => $this->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $this->email, 'password' => 'a-long-password'])->assertRedirect('/library');
    }
}
