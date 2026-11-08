<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reset_password_with_email_otp(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);
        $user = User::where('username', 'guru')->firstOrFail();
        DB::table('users')->where('id', $user->getKey())->update(['email' => 'guru@example.com']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'));

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('name="username"', false);

        $this->post(route('password.email'), ['username' => 'guru'])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHas('status');

        $code = null;
        Notification::assertSentTo($user, PasswordResetOtpNotification::class, function (PasswordResetOtpNotification $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });
        $this->assertNotNull($code);

        $this->get(route('password.reset'))
            ->assertOk()
            ->assertSee('Kode OTP')
            ->assertSee('name="otp"', false);

        $this->post(route('password.update'), [
            'otp' => $code,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('password-baru-123', $user->password));

        $this->post(route('password.update'), [
            'otp' => $code,
            'password' => 'password-baru-456',
            'password_confirmation' => 'password-baru-456',
        ])->assertSessionHasErrors('otp');
    }

    public function test_password_reset_request_fails_if_email_or_user_not_registered(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);

        // 1. Tes email yang tidak terdaftar
        $responseEmail = $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'tidak-terdaftar@example.com']);

        $responseEmail->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['username']);
        $this->assertEquals('Email tidak terdaftar.', session('errors')->first('username'));

        // 2. Tes username yang tidak terdaftar di role manapun
        $responseUser = $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'user_palsu']);

        $responseUser->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['username']);
        $this->assertEquals('Email tidak terdaftar.', session('errors')->first('username'));

        // 3. User ada di database tapi role_id tidak valid (tidak terdaftar di role manapun)
        User::create([
            'username' => 'user_tanpa_role',
            'email' => 'tanparole@example.com',
            'password' => Hash::make('password123'),
            'fullname' => 'Tanpa Role',
            'role_id' => 99,
        ]);
        $responseInvalidRole = $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'tanparole@example.com']);

        $responseInvalidRole->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['username']);
        $this->assertEquals('Email tidak terdaftar.', session('errors')->first('username'));

        Notification::assertNothingSent();
        $this->get(route('password.request'))->assertOk();
    }

    public function test_username_without_recovery_email_is_not_sent_an_otp(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'guru'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['username']);

        Notification::assertNothingSent();
    }

    public function test_invalid_otp_is_rejected_and_attempts_are_counted(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);
        $user = User::where('username', 'guru')->firstOrFail();
        DB::table('users')->where('id', $user->getKey())->update(['email' => 'guru@example.com']);

        $this->post(route('password.email'), ['username' => 'guru']);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'otp' => '000000',
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHasErrors('otp');

        $this->assertDatabaseHas('password_reset_otps', [
            'user_id' => $user->getKey(),
            'attempts' => 1,
        ]);
    }

    public function test_account_profile_can_save_a_unique_recovery_email(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('username', 'guru')->firstOrFail();

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'fullname' => $user->fullname,
                'username' => $user->username,
                'email' => 'GURU@example.com',
            ])
            ->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'email' => 'guru@example.com',
        ]);
    }
}
