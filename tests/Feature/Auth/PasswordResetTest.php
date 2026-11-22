<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FND-03 (SYM-3): Reset kata sandi lewat tautan email berbatas waktu.
 */
#[Group('SYM-3')]
class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $status = 'AKTIF'): User
    {
        return User::create([
            'nama' => 'Pengguna Uji Reset',
            'email' => 'reset_test_'.strtolower($status).'@symbionusa.test',
            'role' => 'BUYER',
            'password_hash' => 'sandi-lama-123',
            'status' => $status,
        ]);
    }

    public function test_login_page_links_to_forgot_password(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('password.request'), false);
    }

    public function test_forgot_password_page_is_displayed(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Lupa kata sandi');
    }

    public function test_reset_link_is_sent_to_active_user(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => strtoupper($user->email)])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('success', PasswordResetLinkController::GENERIC_MESSAGE);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unknown_and_inactive_emails_get_the_same_message_and_no_email(): void
    {
        Notification::fake();
        $inactive = $this->makeUser('NONAKTIF');

        foreach (['tidak_terdaftar@symbionusa.test', $inactive->email] as $email) {
            $this->from('/forgot-password')
                ->post('/forgot-password', ['email' => $email])
                ->assertRedirect('/forgot-password')
                ->assertSessionHas('success', PasswordResetLinkController::GENERIC_MESSAGE);
        }

        Notification::assertNothingSent();
    }

    public function test_email_is_required_to_request_a_link(): void
    {
        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => ''])
            ->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
    }

    public function test_reset_email_link_opens_the_reset_form(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))
                ->assertOk()
                ->assertSee('Buat kata sandi baru')
                ->assertSee($user->email);

            return str_contains($mail->actionUrl, '/reset-password/'.$notification->token)
                && $mail->subject === 'Atur ulang kata sandi SymbioNusa';
        });
    }

    public function test_password_can_be_reset_with_a_valid_token_and_used_to_login(): void
    {
        $user = $this->makeUser();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandi-baru-456',
            'password_confirmation' => 'sandi-baru-456',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('sandi-baru-456', $user->fresh()->password_hash));

        $this->post('/login', ['email' => $user->email, 'password' => 'sandi-baru-456'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_token_is_rejected_and_password_is_unchanged(): void
    {
        $user = $this->makeUser();

        $this->from('/reset-password/token-salah')
            ->post('/reset-password', [
                'token' => 'token-salah',
                'email' => $user->email,
                'password' => 'sandi-baru-456',
                'password_confirmation' => 'sandi-baru-456',
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('sandi-lama-123', $user->fresh()->password_hash));
    }

    public function test_new_password_must_be_confirmed_and_at_least_eight_characters(): void
    {
        $user = $this->makeUser();
        $token = Password::createToken($user);

        $this->from('/reset-password/'.$token)
            ->post('/reset-password', [
                'token' => $token,
                'email' => $user->email,
                'password' => 'pendek',
                'password_confirmation' => 'berbeda',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('sandi-lama-123', $user->fresh()->password_hash));
    }
}
