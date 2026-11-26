<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FND-02 (SYM-2): Logout mengakhiri sesi aktif dan meregenerasi token CSRF.
 */
#[Group('SYM-2')]
class LogoutTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(): User
    {
        return User::create([
            'nama' => 'Pengguna Uji Logout',
            'email' => 'logout_test@symbionusa.test',
            'role' => 'BUYER',
            'password_hash' => 'rahasia-123',
            'status' => 'AKTIF',
        ]);
    }

    public function test_user_can_logout_and_session_is_ended(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user);
        $this->session(['_token' => 'token-lama', 'data_sesi' => 'harus-hilang']);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
        $response->assertSessionMissing('data_sesi');
        $this->assertGuest();
        $this->assertNotSame('token-lama', session()->token());
    }

    public function test_dashboard_is_not_accessible_after_logout(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/logout');

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logout_requires_post(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/logout')->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_posting_logout_is_redirected_to_login(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }

    public function test_dashboard_has_logout_form_with_csrf_token(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('name="_token"', false);
    }
}
