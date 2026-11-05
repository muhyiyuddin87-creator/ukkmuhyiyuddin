<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuruJournalEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_edit_their_saved_journal_and_keep_its_photo(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $journalId = $this->createJournal($teacher, 'jurnal_photos/bukti.jpg');

        $this->actingAs($teacher)
            ->get(route('guru.jurnal', ['edit' => $journalId]))
            ->assertOk()
            ->assertSee('Materi Lama')
            ->assertSee('Simpan Perubahan');

        $this->patch(route('guru.jurnal.update', $journalId), [
            'schedule_id' => DB::table('journals')->where('id', $journalId)->value('schedule_id'),
            'date' => '2026-10-20',
            'topic_description' => 'Materi Diperbarui',
        ])
            ->assertRedirect(route('guru.jurnal'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('journals', [
            'id' => $journalId,
            'date' => '2026-10-20',
            'topic_description' => 'Materi Diperbarui',
            'photo_path' => 'jurnal_photos/bukti.jpg',
        ]);
    }

    public function test_guru_cannot_edit_another_teachers_journal_or_use_their_schedule(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $otherTeacherId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'username' => 'guru.lain',
            'password' => bcrypt('password'),
            'fullname' => 'Guru Lain',
            'created_at' => now(),
        ]);
        $otherTeacher = User::findOrFail($otherTeacherId);
        $journalId = $this->createJournal($otherTeacher);

        $this->actingAs($teacher)
            ->get(route('guru.jurnal', ['edit' => $journalId]))
            ->assertNotFound();

        $this->patch(route('guru.jurnal.update', $journalId), [
            'schedule_id' => DB::table('journals')->where('id', $journalId)->value('schedule_id'),
            'date' => '2026-10-20',
            'topic_description' => 'Percobaan edit',
        ])->assertNotFound();

        $this->post(route('guru.jurnal.store'), [
            'schedule_id' => DB::table('journals')->where('id', $journalId)->value('schedule_id'),
            'date' => '2026-10-20',
            'topic_description' => 'Percobaan jurnal',
        ])->assertSessionHasErrors('schedule_id');
    }

    private function createJournal(User $teacher, ?string $photoPath = null): int
    {
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => 'Jumat',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-10-16',
            'topic_description' => 'Materi Lama',
            'photo_path' => $photoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
