<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FND-01 (SYM-1): Login dengan email dan password_hash, diarahkan ke dasbor.
 */
#[Group('SYM-1')]
class LoginTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $status = 'AKTIF', string $role = 'SELLER'): User
    {
        return User::create([
            'nama' => 'Pengguna Uji Login',
            'email' => 'login_test_'.strtolower($role).'_'.strtolower($status).'@symbionusa.test',
            'role' => $role,
            'password_hash' => 'rahasia-123',
            'status' => $status,
        ]);
    }

    public function test_login_page_is_displayed_without_demo_shortcuts_outside_local(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk ke Platform');
        $response->assertDontSee('Akses cepat akun demo');
        $response->assertDontSee('/login/demo/', false);
    }

    public function test_active_user_can_login_and_is_redirected_to_dashboard(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia-123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_is_checked_against_password_hash_column(): void
    {
        $user = $this->makeUser();

        $this->assertNotSame('rahasia-123', $user->password_hash);
        $this->assertSame($user->password_hash, $user->getAuthPassword());
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->makeUser();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser('NONAKTIF');

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_and_password_are_required(): void
    {
        $response = $this->from('/login')->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_user_is_sent_to_the_intended_page_after_login(): void
    {
        $admin = $this->makeUser('AKTIF', 'ADMIN');

        $this->get('/admin/kategori')->assertRedirect('/login');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'rahasia-123',
        ]);

        $response->assertRedirect('/admin/kategori');
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
    }

    public function test_demo_login_route_is_not_available_outside_local_environment(): void
    {
        $this->post('/login/demo/admin')->assertNotFound();
        $this->get('/login/as/admin')->assertNotFound();
        $this->assertGuest();
    }
}
