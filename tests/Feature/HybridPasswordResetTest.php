<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HybridPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_without_email_gets_helpful_homeroom_teacher_guidance(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);

        $studentUser = User::where('username', 'siswa')->firstOrFail();
        $this->assertNull($studentUser->email);

        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'siswa']);

        $response->assertRedirect(route('password.request'))
            ->assertSessionHas('info_siswa');

        Notification::assertNothingSent();
    }

    public function test_user_can_request_otp_using_email_address_instead_of_username(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);

        $guru = User::where('username', 'guru')->firstOrFail();
        $guru->update(['email' => 'guruku@sekolah.sch.id']);

        // Masukkan alamat email (bukan username)
        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['username' => 'GURUKU@sekolah.sch.id']);

        $response->assertRedirect(route('password.reset'))
            ->assertSessionHas('status');

        Notification::assertSentTo($guru, \App\Notifications\PasswordResetOtpNotification::class);
    }

    public function test_homeroom_teacher_can_view_class_students_and_reset_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Cari guru dan beri penugasan wali kelas
        $guru = User::where('username', 'guru')->firstOrFail();
        $schoolClass = DB::table('school_classes')->first();
        if (! $schoolClass) {
            $classId = DB::table('school_classes')->insertGetId([
                'name' => 'X RPL 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $className = 'X RPL 1';
        } else {
            $classId = $schoolClass->id;
            $className = $schoolClass->name;
        }

        DB::table('user_school_classes')->insertOrIgnore([
            'user_id' => $guru->id,
            'school_class_id' => $classId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Buat siswa di kelas tersebut
        $studentId = DB::table('siswas')->insertGetId([
            'name' => 'Budi Santoso',
            'nis' => '12345678',
            'kelas' => $className,
            'jurusan' => 'RPL',
            'alamat' => 'Jl. Pendidikan No. 1',
        ]);

        $studentUser = User::create([
            'username' => 'budirpl',
            'password' => Hash::make('oldpassword'),
            'fullname' => 'Budi Santoso',
            'role_id' => 3,
            'student_id' => $studentId,
            'is_active' => true,
        ]);

        // Wali Kelas buka halaman daftar siswa kelas
        $this->actingAs($guru)
            ->get(route('wali-kelas.siswa'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('budirpl');

        // Wali Kelas reset sandi siswa
        $this->actingAs($guru)
            ->post(route('wali-kelas.siswa.reset_password', $studentUser->id))
            ->assertRedirect(route('wali-kelas.siswa'))
            ->assertSessionHas('reset_info');

        $studentUser->refresh();
        $this->assertTrue((bool) $studentUser->must_change_password);
        $this->assertTrue(Hash::check('12345678', $studentUser->password));
    }

    public function test_user_with_must_change_password_is_forced_to_update_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $studentUser = User::where('username', 'siswa')->firstOrFail();

        $studentUser->update([
            'password' => Hash::make('temp12345'),
            'must_change_password' => true,
        ]);

        // Login dengan sandi sementara
        $response = $this->post(route('login.post'), [
            'username' => 'siswa',
            'password' => 'temp12345',
        ]);

        $response->assertRedirect(route('password.force_change'));

        // Coba akses dashboard sebelum ganti sandi -> harus tetap diarahkan ke force change password
        $this->actingAs($studentUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.force_change'));

        // Akses halaman form force change password
        $this->actingAs($studentUser)
            ->get(route('password.force_change'))
            ->assertOk()
            ->assertSee('Ganti Sandi Akun Anda');

        // Submit sandi baru
        $updateResponse = $this->actingAs($studentUser)
            ->post(route('password.force_change.update'), [
                'password' => 'rahasia12345',
                'password_confirmation' => 'rahasia12345',
                'email' => 'siswa.budi@example.com',
            ]);

        $updateResponse->assertRedirect(route('dashboard'));

        $studentUser->refresh();
        $this->assertFalse((bool) $studentUser->must_change_password);
        $this->assertTrue(Hash::check('rahasia12345', $studentUser->password));
        $this->assertEquals('siswa.budi@example.com', $studentUser->email);

        // Setelah diganti, dashboard bisa diakses secara normal (redirect ke role dashboard)
        $this->actingAs($studentUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('dashboard.role', ['role' => 'siswa']));
    }

    public function test_waka_can_reset_any_account_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $guru = User::where('username', 'guru')->firstOrFail();

        $this->actingAs($waka)
            ->post(route('waka.accounts.reset_password', $guru->id))
            ->assertRedirect(route('waka.accounts'))
            ->assertSessionHas('reset_info');

        $guru->refresh();
        $this->assertTrue((bool) $guru->must_change_password);
    }
}
