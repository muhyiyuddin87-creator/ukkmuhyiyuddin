<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoRoleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_accounts_are_seeded_for_each_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['username' => 'waka', 'role_id' => 1]);
        $this->assertDatabaseHas('users', ['username' => 'guru', 'role_id' => 2]);
        $this->assertDatabaseHas('users', ['username' => 'siswa', 'role_id' => 3]);
        $this->assertDatabaseHas('users', ['username' => 'kesiswaan', 'role_id' => 4]);
        $this->assertDatabaseHas('users', ['username' => 'ortu', 'role_id' => 6]);
    }

    public function test_login_page_hides_demo_credentials_but_keeps_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Lupa sandi?')
            ->assertDontSee('Demo akun:')
            ->assertDontSee('waka123')
            ->assertDontSee('guru123')
            ->assertDontSee('siswa123')
            ->assertDontSee('kesiswaan123');
    }

    public function test_waka_can_login_and_redirect_to_dashboard_waka(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'username' => 'waka',
            'password' => 'waka123',
        ]);

        $response->assertRedirect('/dashboard/waka');
        $this->assertAuthenticated();
    }

    public function test_each_role_dashboard_displays_the_active_cycle(): void
    {
        $this->seed(DatabaseSeeder::class);
        DB::table('system_configurations')->insert([
            'active_cycle' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            1 => 'waka',
            2 => 'guru',
            3 => 'siswa',
            4 => 'kesiswaan',
        ] as $roleId => $roleSlug) {
            $user = User::where('role_id', $roleId)->firstOrFail();

            $this->actingAs($user)
                ->get(route('dashboard.role', ['role' => $roleSlug]))
                ->assertOk()
                ->assertSee('Saat ini putaran ke-2');
        }
    }

    public function test_authenticated_user_is_redirected_to_their_role_dashboard_from_root(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', [
            'username' => 'waka',
            'password' => 'waka123',
        ]);

        $this->get('/')->assertRedirect('/dashboard/waka');
    }

    public function test_plaintext_passwords_are_rehashed_during_login(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('username', 'waka')->first();
        $user->password = 'waka123';
        $user->save();

        $response = $this->post('/login', [
            'username' => 'waka',
            'password' => 'waka123',
        ]);

        $response->assertRedirect('/dashboard/waka');
        $user->refresh();
        $this->assertTrue(Hash::check('waka123', $user->password));
        $this->assertStringStartsWith('$2', $user->password);
    }
}
