<?php

namespace App\Services;

use App\Support\SchoolTimetable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AutoScheduleGeneratorService
{
    /**
     * Mengambil daftar slot sesi mengajar per hari (tanpa upacara, literasi, atau piket).
     */
    public static function getTeachingPeriodSlots(): array
    {
        $slots = [];
        foreach (SchoolTimetable::DAYS as $day) {
            $periods = SchoolTimetable::periodsForDay($day);
            $daySlots = [];
            $seq = 1;

            foreach ($periods as $code => $data) {
                // Abaikan sesi non-KBM (upacara, literasi, piket/shalat)
                if (in_array($code, ['upacara', 'literasi', 'piket'], true)) {
                    continue;
                }

                // Kelompokkan sesi pagi/siang berdasarkan jeda istirahat
                // Senin-Kamis: 1-3 (Pagi 1), 4-6 (Pagi 2), 7-10 (Siang)
                // Jumat: 1-3 (Pagi 1), 4-6 (Pagi 2), 7-8 (Siang)
                $group = 1;
                if ($seq >= 4 && $seq <= 6) {
                    $group = 2;
                } elseif ($seq >= 7) {
                    $group = 3;
                }

                $daySlots[] = [
                    'code' => $code,
                    'label' => $data['label'],
                    'start' => substr($data['start'], 0, 5),
                    'end' => substr($data['end'], 0, 5),
                    'seq' => $seq,
                    'group' => $group,
                ];
                $seq++;
            }

            $slots[$day] = $daySlots;
        }

        return $slots;
    }

    /**
     * Eksekusi penyusunan jadwal otomatis bebas bentrok (Constraint Satisfaction Engine).
     *
     * @param array $allocations Daftar alokasi: [['teacher_id' => ..., 'subject' => ..., 'class_name' => ..., 'room_id' => ..., 'jp' => ..., 'cycle_type' => ...], ...]
     * @param int $cycleNumber Putaran target (1 atau 2)
     * @param string $academicYear Tahun ajaran (misal '2026/2027')
     * @param bool $fresh Hapus jadwal lama di putaran tersebut sebelum menyusun baru
     * @return array Hasil eksekusi
     */
    public function generate(array $allocations, int $cycleNumber, string $academicYear, bool $fresh = false): array
    {
        if (empty($allocations)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data alokasi yang dimasukkan.',
                'scheduled_count' => 0,
                'total_jp' => 0,
                'unassigned' => [],
            ];
        }

        $allDaySlots = self::getTeachingPeriodSlots();

        // 1. Matriks okupansi (Peta kesibukan)
        $teacherBusy = [];
        $classBusy = [];
        $roomBusy = [];
        $classDailyJp = [];
        $classSubjectDays = [];

        // Inisialisasi peta okupansi untuk seluruh hari
        foreach (SchoolTimetable::DAYS as $day) {
            $teacherBusy[$day] = [];
            $classBusy[$day] = [];
            $roomBusy[$day] = [];
            $classDailyJp[$day] = [];
        }

        // 2. Jika bukan mode fresh, muat jadwal lama yang sudah ada di database ke matriks okupansi
        if (! $fresh) {
            $existingSchedules = DB::table('schedules')
                ->where('cycle_number', $cycleNumber)
                ->where(function ($q) use ($academicYear) {
                    $q->where('academic_year', $academicYear)->orWhereNull('academic_year');
                })
                ->get();

            foreach ($existingSchedules as $ex) {
                $day = $ex->day_of_week;
                if (! isset($allDaySlots[$day])) continue;

                $start = substr($ex->start_time, 0, 5);
                $end = substr($ex->end_time, 0, 5);

                foreach ($allDaySlots[$day] as $slot) {
                    // Cek overlap waktu
                    if ($slot['start'] < $end && $slot['end'] > $start) {
                        $code = $slot['code'];
                        $teacherBusy[$day][$ex->teacher_id][$code] = true;

                        if (! empty($ex->class_name)) {
                            $classBusy[$day][$ex->class_name][$code] = true;
                            $classDailyJp[$day][$ex->class_name] = ($classDailyJp[$day][$ex->class_name] ?? 0) + 1;
                            $classSubjectDays[$ex->class_name][$ex->subject][$day] = true;
                        }

                        if (! empty($ex->room_id)) {
                            $roomBusy[$day][$ex->room_id][$code] = true;
                        }
                    }
                }
            }
        }

        // 3. Pecah setiap alokasi menjadi sesi/blok pertemuan yang ideal
        // Contoh: 4 JP -> 1x4 JP atau 2x2 JP; 3 JP -> 1x3 JP; 2 JP -> 1x2 JP
        $blocksToSchedule = [];
        foreach ($allocations as $idx => $alloc) {
            $jp = max(1, (int) ($alloc['jp'] ?? 2));
            $subject = trim((string) ($alloc['subject'] ?? 'Mata Pelajaran'));
            $className = trim((string) ($alloc['class_name'] ?? ''));
            $teacherId = (int) ($alloc['teacher_id'] ?? 0);
            $roomId = ! empty($alloc['room_id']) ? (int) $alloc['room_id'] : null;
            $cycleType = in_array($alloc['cycle_type'] ?? '', ['Jurusan', 'Normada', 'Regular'], true)
                ? $alloc['cycle_type']
                : 'Normada';

            if ($teacherId <= 0 || empty($className) || empty($subject)) {
                continue;
            }

            // Aturan pemecahan blok JP:
            // Jika JP <= 4: buat 1 sesi pertemuan utuh
            // Jika JP = 5: pecah jadi 3 JP + 2 JP
            // Jika JP = 6: pecah jadi 3 JP + 3 JP atau 4 JP + 2 JP
            // Jika JP > 6: pecah per 3-4 JP
            $chunks = [];
            if ($jp <= 4) {
                $chunks[] = $jp;
            } elseif ($jp === 5) {
                $chunks = [3, 2];
            } elseif ($jp === 6) {
                $chunks = [3, 3];
            } else {
                $rem = $jp;
                while ($rem > 0) {
                    $take = min(4, $rem);
                    $chunks[] = $take;
                    $rem -= $take;
                }
            }

            foreach ($chunks as $chunkJp) {
                $blocksToSchedule[] = [
                    'alloc_id' => $idx,
                    'teacher_id' => $teacherId,
                    'subject' => $subject,
                    'class_name' => $className,
                    'room_id' => $roomId,
                    'jp' => $chunkJp,
                    'cycle_type' => $cycleType,
                    // Bobot prioritas: Blok yang butuh ruangan khusus (Lab) atau JP lebih besar didahulukan
                    'priority' => ($roomId ? 100 : 0) + ($chunkJp * 10),
                ];
            }
        }

        // Urutkan blok dari prioritas tertinggi (Most Constrained First)
        usort($blocksToSchedule, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        // 4. Proses penempatan slot (Slot Finding Engine)
        $scheduledItems = [];
        $unassignedBlocks = [];

        foreach ($blocksToSchedule as $block) {
            $jpNeeded = $block['jp'];
            $teacherId = $block['teacher_id'];
            $className = $block['class_name'];
            $roomId = $block['room_id'];
            $subject = $block['subject'];

            $bestCandidate = null;
            $bestScore = -999999;

            // Prioritaskan hari dengan beban JP kelas yang paling sedikit agar merata
            $daysByLoad = SchoolTimetable::DAYS;
            usort($daysByLoad, function ($dA, $dB) use ($classDailyJp, $className) {
                $loadA = $classDailyJp[$dA][$className] ?? 0;
                $loadB = $classDailyJp[$dB][$className] ?? 0;
                return $loadA <=> $loadB;
            });

            foreach ($daysByLoad as $day) {
                $daySlots = $allDaySlots[$day];
                $slotCount = count($daySlots);

                if ($slotCount < $jpNeeded) {
                    continue;
                }

                // Cek kemungkinan penempatan blok berurutan (i s/d i + jpNeeded - 1)
                for ($i = 0; $i <= ($slotCount - $jpNeeded); $i++) {
                    $candidateSlots = array_slice($daySlots, $i, $jpNeeded);

                    // Periksa apakah slot dalam 1 grup sesi (tidak terpotong istirahat)
                    $firstGroup = $candidateSlots[0]['group'];
                    $sameGroup = true;
                    foreach ($candidateSlots as $cs) {
                        if ($cs['group'] !== $firstGroup) {
                            $sameGroup = false;
                            break;
                        }
                    }

                    // Periksa bentrok untuk setiap jam pelajaran dalam blok ini
                    $isConflict = false;
                    foreach ($candidateSlots as $cs) {
                        $code = $cs['code'];

                        // 1. Bentrok Guru
                        if (! empty($teacherBusy[$day][$teacherId][$code])) {
                            $isConflict = true;
                            break;
                        }

                        // 2. Bentrok Kelas
                        if (! empty($classBusy[$day][$className][$code])) {
                            $isConflict = true;
                            break;
                        }

                        // 3. Bentrok Ruangan
                        if ($roomId && ! empty($roomBusy[$day][$roomId][$code])) {
                            $isConflict = true;
                            break;
                        }
                    }

                    if ($isConflict) {
                        continue;
                    }

                    // Hitung skor kesesuaian slot (Fitness Heuristic)
                    $score = 0;

                    // Sangat diutamakan: Berada dalam 1 kelompok sesi (tidak terpotong istirahat)
                    if ($sameGroup) {
                        $score += 500;
                    }

                    // Hindari mapel yang sama diajarkan lebih dari 1 kali di hari yang sama
                    if (empty($classSubjectDays[$className][$subject][$day])) {
                        $score += 300;
                    } else {
                        $score -= 200;
                    }

                    // Penyeimbang beban hari (utamakan hari yang masih sedikit jam belajarnya)
                    $currentDayLoad = $classDailyJp[$day][$className] ?? 0;
                    $score -= ($currentDayLoad * 25);

                    $startSeq = $candidateSlots[0]['seq'];
                    $endSeq = end($candidateSlots)['seq'];

                    // Heuristik Pemadatan Jadwal dari Jam Pertama (Pack from Jam ke-1):
                    $isDayEmptyForClass = ($currentDayLoad === 0);
                    if ($isDayEmptyForClass) {
                        // Jika hari ini belum ada jadwal sama sekali untuk kelas ini, prioritaskan mulai dari Jam ke-1
                        if ($startSeq === 1) {
                            $score += 2500;
                        } else {
                            // Penalti jika memulai di jam 2, 3, dst padahal jam 1 masih kosong
                            $score -= ($startSeq * 500);
                        }
                    } else {
                        // Jika hari ini sudah ada jadwal untuk kelas ini:
                        // Cek apakah jam ke-1 untuk kelas ini sudah terisi
                        $period1Filled = ! empty($classBusy[$day][$className]['period_1']);
                        if (! $period1Filled) {
                            if ($startSeq === 1) {
                                $score += 3000; // Segera isi jam ke-1 yang masih bolong!
                            } else {
                                $score -= 1200; // Jangan tambah jadwal di siang jika jam 1 belum terisi
                            }
                        }

                        // Cek kekompakan (Contiguity): Apakah slot ini menempel langsung sebelum atau sesudah slot yang sudah ada?
                        $prevSeq = $startSeq - 1;
                        $nextSeq = $endSeq + 1;
                        $prevCode = "period_{$prevSeq}";
                        $nextCode = "period_{$nextSeq}";

                        $attached = false;
                        if (! empty($classBusy[$day][$className][$prevCode])) {
                            $attached = true;
                            $score += 1000;
                        }
                        if (! empty($classBusy[$day][$className][$nextCode])) {
                            $attached = true;
                            $score += 1000;
                        }

                        // Jika tidak menempel sama sekali (menciptakan jeda/bolong di tengah hari), beri penalti
                        if (! $attached) {
                            $score -= 800;
                        }
                    }

                    // Bonus bertingkat untuk jam lebih awal agar sisa jam kosong berada di akhir hari (pulang awal)
                    $score += max(0, (11 - $startSeq) * 20);

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestCandidate = [
                            'day' => $day,
                            'slots' => $candidateSlots,
                            'start_time' => $candidateSlots[0]['start'] . ':00',
                            'end_time' => end($candidateSlots)['end'] . ':00',
                        ];
                    }
                }
            }

            // Jika slot terbaik ditemukan, kunci okupansi
            if ($bestCandidate !== null) {
                $day = $bestCandidate['day'];
                foreach ($bestCandidate['slots'] as $cs) {
                    $code = $cs['code'];
                    $teacherBusy[$day][$teacherId][$code] = true;
                    $classBusy[$day][$className][$code] = true;
                    if ($roomId) {
                        $roomBusy[$day][$roomId][$code] = true;
                    }
                }

                $classDailyJp[$day][$className] = ($classDailyJp[$day][$className] ?? 0) + $jpNeeded;
                $classSubjectDays[$className][$subject][$day] = true;

                $scheduledItems[] = [
                    'teacher_id' => $teacherId,
                    'class_name' => $className,
                    'room_id' => $roomId,
                    'subject' => $subject,
                    'day_of_week' => $day,
                    'start_time' => $bestCandidate['start_time'],
                    'end_time' => $bestCandidate['end_time'],
                    'cycle_number' => $cycleNumber,
                    'cycle_type' => $block['cycle_type'],
                    'academic_year' => $academicYear,
                    'jp' => $jpNeeded,
                ];
            } else {
                // Jika blok 4 JP gagal ditempatkan dalam 1 sesi utuh, pecah menjadi 2x2 JP agar fleksibel di pagi hari
                if ($jpNeeded === 4 && empty($block['is_split_fallback'])) {
                    $splitBlock1 = $block;
                    $splitBlock1['jp'] = 2;
                    $splitBlock1['priority'] = ($roomId ? 100 : 0) + 20;
                    $splitBlock1['is_split_fallback'] = true;

                    $splitBlock2 = $block;
                    $splitBlock2['jp'] = 2;
                    $splitBlock2['priority'] = ($roomId ? 100 : 0) + 20;
                    $splitBlock2['is_split_fallback'] = true;

                    array_unshift($blocksToSchedule, $splitBlock1, $splitBlock2);
                    continue;
                }

                $unassignedBlocks[] = $block;
            }
        }

        // 5. Simpan ke database dalam 1 transaksi aman
        if (! empty($scheduledItems)) {
            DB::transaction(function () use ($scheduledItems, $fresh, $cycleNumber, $academicYear) {
                if ($fresh) {
                    // Dapatkan semua ID jadwal lama pada putaran dan tahun ajaran bersangkutan
                    $oldScheduleIds = DB::table('schedules')
                        ->where('cycle_number', $cycleNumber)
                        ->where(function ($q) use ($academicYear) {
                            $q->where('academic_year', $academicYear)->orWhereNull('academic_year');
                        })
                        ->pluck('id')
                        ->all();

                    if (! empty($oldScheduleIds)) {
                        // Bersihkan presensi dan jurnal yang merujuk ke jadwal ini secara bertahap agar tidak memicu RESTRICT foreign key
                        foreach (array_chunk($oldScheduleIds, 500) as $idChunk) {
                            $journalIds = DB::table('journals')->whereIn('schedule_id', $idChunk)->pluck('id')->all();
                            if (! empty($journalIds)) {
                                foreach (array_chunk($journalIds, 500) as $jChunk) {
                                    DB::table('attendances')->whereIn('journal_id', $jChunk)->delete();
                                    DB::table('journals')->whereIn('id', $jChunk)->delete();
                                }
                            }
                            DB::table('schedules')->whereIn('id', $idChunk)->delete();
                        }
                    }
                }

                $rowsToInsert = [];
                $now = now();
                foreach ($scheduledItems as $item) {
                    $cleanClassName = mb_substr(trim((string) $item['class_name']), 0, 50);
                    $cleanSubject = mb_substr(trim((string) $item['subject']), 0, 100);

                    if (empty($cleanClassName) || $cleanClassName === '-') {
                        continue;
                    }

                    $rowsToInsert[] = [
                        'teacher_id' => (int) $item['teacher_id'],
                        'class_name' => $cleanClassName,
                        'room_id' => ! empty($item['room_id']) ? (int) $item['room_id'] : null,
                        'subject' => $cleanSubject,
                        'day_of_week' => $item['day_of_week'],
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                        'cycle_number' => $item['cycle_number'],
                        'cycle_type' => $item['cycle_type'],
                        'academic_year' => $item['academic_year'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Insert bertahap per 100 baris untuk efisiensi
                foreach (array_chunk($rowsToInsert, 100) as $chunk) {
                    DB::table('schedules')->insert($chunk);
                }
            });
        }

        $totalJpScheduled = array_sum(array_column($scheduledItems, 'jp'));

        return [
            'success' => count($scheduledItems) > 0,
            'scheduled_count' => count($scheduledItems),
            'total_jp' => $totalJpScheduled,
            'unassigned' => $unassignedBlocks,
            'unassigned_count' => count($unassignedBlocks),
        ];
    }

    /**
     * Mengambil daftar alokasi secara otomatis dari data yang sudah ada di database/sistem
     * tanpa mewajibkan pengguna mengunggah berkas Excel secara manual.
     */
    public function getAllocationsFromDatabase(?int $sourceCycleNumber = null, ?string $academicYear = null): array
    {
        // 1. Prioritaskan pembacaan master alokasi yang tersimpan di sistem project
        $masterPath = public_path('downloads/DATA_MASTER_DAN_ALOKASI_JADWAL.xlsx');
        if (file_exists($masterPath)) {
            $fromMaster = $this->parseAllocationFile($masterPath);
            if (! empty($fromMaster)) {
                return $fromMaster;
            }
        }

        // 2. Ekstrak langsung dari tabel schedules yang ada di database
        $query = DB::table('schedules');
        if ($sourceCycleNumber) {
            $query->where('cycle_number', $sourceCycleNumber);
        }
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $existing = $query->get();
        if ($existing->isNotEmpty()) {
            $allocations = [];
            foreach ($existing as $s) {
                if (empty($s->class_name) || empty($s->subject) || empty($s->teacher_id)) {
                    continue;
                }

                $day = $s->day_of_week;
                $start = substr($s->start_time, 0, 5);
                $end = substr($s->end_time, 0, 5);

                $periods = SchoolTimetable::periodsForDay($day);
                $jp = 0;
                foreach ($periods as $code => $p) {
                    if (in_array($code, ['upacara', 'literasi', 'piket'], true)) continue;
                    if ($p['start'] < $end && $p['end'] > $start) {
                        $jp++;
                    }
                }
                if ($jp <= 0) {
                    $jp = 2;
                }

                $allocations[] = [
                    'teacher_id' => (int) $s->teacher_id,
                    'subject' => mb_substr(trim($s->subject), 0, 100),
                    'class_name' => mb_substr(trim($s->class_name), 0, 50),
                    'room_id' => ! empty($s->room_id) ? (int) $s->room_id : null,
                    'jp' => max(1, min(8, $jp)),
                    'cycle_type' => in_array($s->cycle_type, ['Jurusan', 'Normada', 'Regular'], true) ? $s->cycle_type : 'Normada',
                ];
            }

            if (! empty($allocations)) {
                return $allocations;
            }
        }

        return [];
    }

    /**
     * Membaca dan memetakan berkas spreadsheet alokasi (.xlsx, .xls, .csv).
     */
    public function parseAllocationFile(string $filePath): array
    {
        @ini_set('memory_limit', '512M');
        $spreadsheet = IOFactory::load($filePath);

        // Pilih sheet alokasi secara cerdas: cari sheet yang memiliki Guru, Mapel, DAN Kelas
        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $sh) {
            $title = strtolower($sh->getTitle());
            if (str_contains($title, 'alokasi') || str_contains($title, 'jadwal')) {
                $rowsPreview = $sh->rangeToArray('A1:G3', null, true, true, true);
                $hasG = false; $hasM = false; $hasK = false;
                foreach ($rowsPreview as $rp) {
                    $joined = strtolower(implode(' ', array_filter($rp)));
                    if (str_contains($joined, 'guru') || str_contains($joined, 'pengampu')) $hasG = true;
                    if (str_contains($joined, 'mapel') || str_contains($joined, 'mata pelajaran')) $hasM = true;
                    if (str_contains($joined, 'kelas') || str_contains($joined, 'class')) $hasK = true;
                }
                if ($hasG && $hasM && $hasK) {
                    $sheet = $sh;
                    break;
                }
            }
        }

        if (! $sheet) {
            foreach ($spreadsheet->getAllSheets() as $sh) {
                $rowsPreview = $sh->rangeToArray('A1:Z5', null, true, true, true);
                $hasG = false; $hasM = false; $hasK = false;
                foreach ($rowsPreview as $rp) {
                    $joined = strtolower(implode(' ', array_filter($rp)));
                    if (str_contains($joined, 'guru') || str_contains($joined, 'pengampu')) $hasG = true;
                    if (str_contains($joined, 'mapel') || str_contains($joined, 'mata pelajaran')) $hasM = true;
                    if (str_contains($joined, 'kelas') || str_contains($joined, 'class')) $hasK = true;
                }
                if ($hasG && $hasM && $hasK) {
                    $sheet = $sh;
                    break;
                }
            }
        }

        if (! $sheet) {
            $sheet = $spreadsheet->getSheet(0);
        }

        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return [];
        }

        // Ambil data referensi guru & ruangan dari database untuk pencocokan nama
        $teachers = DB::table('users')
            ->whereIn('role_id', [2, 4])
            ->select('id', 'fullname', 'username', 'nip')
            ->get();

        $rooms = DB::table('ruangan')->select('id_ruangan', 'nama_ruangan')->get();

        $headerRow = null;
        $headerIndices = [];

        // Deteksi header kolom
        foreach ($rows as $rowIndex => $row) {
            $normalizedRow = array_map(fn ($cell) => strtolower(trim((string) $cell)), $row);
            $joined = implode(' ', $normalizedRow);

            if (str_contains($joined, 'guru') || str_contains($joined, 'mapel') || str_contains($joined, 'kelas')) {
                $headerRow = $rowIndex;
                foreach ($normalizedRow as $colLetter => $val) {
                    if (str_contains($val, 'guru') || str_contains($val, 'pengampu') || str_contains($val, 'teacher')) {
                        $headerIndices['teacher'] = $colLetter;
                    } elseif (str_contains($val, 'mapel') || str_contains($val, 'mata pelajaran') || str_contains($val, 'subject')) {
                        $headerIndices['subject'] = $colLetter;
                    } elseif (str_contains($val, 'kelas') || str_contains($val, 'class')) {
                        $headerIndices['class'] = $colLetter;
                    } elseif (str_contains($val, 'ruang') || str_contains($val, 'room') || str_contains($val, 'lab')) {
                        $headerIndices['room'] = $colLetter;
                    } elseif (str_contains($val, 'jp') || str_contains($val, 'jam') || str_contains($val, 'durasi')) {
                        $headerIndices['jp'] = $colLetter;
                    } elseif (str_contains($val, 'jenis') || str_contains($val, 'tipe') || str_contains($val, 'kategori')) {
                        $headerIndices['type'] = $colLetter;
                    }
                }
                break;
            }
        }

        if ($headerRow === null) {
            return [];
        }

        $allocations = [];
        $dataRows = array_slice($rows, $headerRow, null, true);

        foreach ($dataRows as $r) {
            $rawTeacher = trim((string) ($r[$headerIndices['teacher'] ?? 'A'] ?? ''));
            $rawSubject = trim((string) ($r[$headerIndices['subject'] ?? 'B'] ?? ''));
            $rawClass = trim((string) ($r[$headerIndices['class'] ?? 'C'] ?? ''));
            $rawRoom = trim((string) ($r[$headerIndices['room'] ?? 'D'] ?? ''));
            $rawJp = (int) trim((string) ($r[$headerIndices['jp'] ?? 'E'] ?? '2'));
            $rawType = trim((string) ($r[$headerIndices['type'] ?? 'F'] ?? ''));

            // Lewatkan baris yang tidak memiliki guru, mapel, atau kelas valid (misal tanda strip '-')
            if (empty($rawTeacher) || empty($rawSubject) || empty($rawClass) || $rawClass === '-' || strlen($rawClass) < 2) {
                continue;
            }

            // Batasi panjang subject dan class name agar tidak melebihi kapasitas kolom database (VARCHAR 100 & 50)
            $cleanSubject = mb_substr($rawSubject, 0, 100);
            $cleanClass = mb_substr(strtoupper($rawClass), 0, 50);

            // Cocokkan Guru dengan pembersihan case-insensitive yang aman
            $matchedTeacherId = null;
            $cleanRawTeacher = preg_replace('/[^a-z0-9]/', '', strtolower($rawTeacher));

            foreach ($teachers as $t) {
                $cleanFullname = preg_replace('/[^a-z0-9]/', '', strtolower($t->fullname ?? ''));
                $cleanUsername = preg_replace('/[^a-z0-9]/', '', strtolower($t->username ?? ''));

                if ($cleanRawTeacher === $cleanFullname || $cleanRawTeacher === $cleanUsername) {
                    $matchedTeacherId = $t->id;
                    break;
                }
                if ($cleanFullname && strlen($cleanFullname) >= 5 && (str_contains($cleanRawTeacher, $cleanFullname) || str_contains($cleanFullname, $cleanRawTeacher))) {
                    $matchedTeacherId = $t->id;
                    break;
                }
            }

            if (! $matchedTeacherId) {
                continue;
            }

            // Cocokkan Ruangan jika diisi
            $matchedRoomId = null;
            if (! empty($rawRoom)) {
                $cleanRawRoom = preg_replace('/[^a-z0-9]/', '', strtolower($rawRoom));
                foreach ($rooms as $rm) {
                    $cleanRoomName = preg_replace('/[^a-z0-9]/', '', strtolower($rm->nama_ruangan ?? ''));
                    if ($cleanRawRoom === $cleanRoomName || (strlen($cleanRoomName) >= 3 && (str_contains($cleanRawRoom, $cleanRoomName) || str_contains($cleanRoomName, $cleanRawRoom)))) {
                        $matchedRoomId = $rm->id_ruangan;
                        break;
                    }
                }
            }

            $allocations[] = [
                'teacher_id' => $matchedTeacherId,
                'subject' => $cleanSubject,
                'class_name' => $cleanClass,
                'room_id' => $matchedRoomId,
                'jp' => max(1, min(8, $rawJp > 0 ? $rawJp : 2)),
                'cycle_type' => str_contains(strtolower($rawType), 'jurusan') ? 'Jurusan' : 'Normada',
            ];
        }

        return $allocations;
    }
}

