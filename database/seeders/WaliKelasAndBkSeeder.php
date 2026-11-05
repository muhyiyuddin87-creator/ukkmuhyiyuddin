<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaliKelasAndBkSeeder extends Seeder
{
    public static array $assignments = [
        ['1', 'X AK 1', 'ERIDHA DEWI SEPITASARI, S.Pd.', 'CANDRA RAHADIAN, S.Pd'],
        ['2', 'X AK 2', 'SULARDI, S.Pd', 'CANDRA RAHADIAN, S.Pd'],
        ['3', 'X AK 3', 'RISA RESTI AFRIANI, S. Pd.', 'CANDRA RAHADIAN, S.Pd'],
        ['4', 'X LPS 1', 'SRI NUR ARAFAH, S.Pd', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['5', 'X LPS 2', 'MUHAMMAD FURQON, S.Sos', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['6', 'X MP 1', 'MENIK LESTARI, S.PD', 'AISYAH WULANDARI, S.Pd'],
        ['7', 'X MP 2', 'TENTREM YUNIATI LESTARI, S.Pd', 'AISYAH WULANDARI, S.Pd'],
        ['8', 'X BR 1', 'TATIK HERAWATI, S.Pd', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['9', 'X BR 2', 'AGATHA CHRISTIYANTI MANULLANG, SS', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['10', 'X BD', 'AHMAD MUHLASHIN, S.Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['11', 'X TKJ 1', 'TRI SUSILAWATI, S.T., M.Pd.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['12', 'X TKJ 2', 'ANASTASIA RATNA SULISTIYANI, S.Pd', 'CANDRA RAHADIAN, S.Pd'],
        ['13', 'X DKV 1', 'YULI ARBIATININGSIH, S.Pd', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['14', 'X DKV 2', 'YULI ASTUTI, S.Pd.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['15', 'X RPL 1', 'RUSDIYANTO, S.Pd', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['16', 'X RPL 2', 'SURATIMAH, S. Pd. I', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['17', 'XI AK 1', 'MARGIYATI, S.Pd., M.Pd.', 'AISYAH WULANDARI, S.Pd'],
        ['18', 'XI AK 2', 'YANU ISWANTARA, S.Pd.Kor.', 'AISYAH WULANDARI, S.Pd'],
        ['19', 'XI AK 3', 'AJENG FITRI SARASWATI, S.Pd.', 'CANDRA RAHADIAN, S.Pd'],
        ['20', 'XI LPS 1', 'DIAN AWALIATI, S.Pd.,M.Pd.', 'AISYAH WULANDARI, S.Pd'],
        ['21', 'XI LPS 2', 'NOVIA RAHMALINA, S.Pd', 'CANDRA RAHADIAN, S.Pd'],
        ['22', 'XI MP 1', 'HERMI CAHYAWATI, S.Pd. M.S.I', 'AISYAH WULANDARI, S.Pd'],
        ['23', 'XI MP 2', 'UMI KUNTARI, SS.,M.Pd.', 'AISYAH WULANDARI, S.Pd'],
        ['24', 'XI BR 1', 'WAHYUNI, S.Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['25', 'XI BR 2', 'SRI SUNARTINI, S.Pd', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['26', 'XI BD', 'AGNES ENDANG MUDJI RAHAYU, S.Pd., M.Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['27', 'XI TKJ 1', 'AMAT SUKARDI, S.Pd.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['28', 'XI TKJ 2', 'MURDIASIH KADARWATI, S.Pd', 'CANDRA RAHADIAN, S.Pd'],
        ['29', 'XI DKV 1', 'EVA YUNI ASTUTI, S.Pd', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['30', 'XI DKV 2', 'JUNIA SARI, S. Pd.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['31', 'XI RPL 1', 'RENY MURNI HIDAYATI, S.Pd.', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['32', 'XI RPL 2', 'SUJAR HARTONO, S.Pd.', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['33', 'XII AK 1', 'SRI WIDAYATI, S.Pd., M.Pd.', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['34', 'XII AK 2', 'ISTI FITRIANA, S.Pd', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['35', 'XII AK 3', 'RINI BHINTARTI, S.Pd', 'AISYAH WULANDARI, S.Pd'],
        ['36', 'XII AK 4', 'LAUHIN ILMI AGUSTIN, S.Pd.', 'CANDRA RAHADIAN, S.Pd'],
        ['37', 'XII LPS', 'RIRIH DAMAYANTI WISNU GIYONO, S.E., M.Acc.', 'CANDRA RAHADIAN, S.Pd'],
        ['38', 'XII MP 1', 'FAILLA ROCHMAYANTI, S.Pd., M.Pd.', 'AISYAH WULANDARI, S.Pd'],
        ['39', 'XII MP 2', 'NINDA APRILIA, S.Pd.', 'AISYAH WULANDARI, S.Pd'],
        ['40', 'XII BR 1', 'SRI SUNARSIH, S.Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['41', 'XII BR 2', 'RADEN RARA BERNADETA SRI WIDYASTUTI, S.Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['42', 'XII BD', 'PUTRI WULANJARI, S. Pd.', 'ALIVI SAZKIA HAWIE, S.Pd'],
        ['43', 'XII TKJ 1', 'KURNIA SAFITRI, S.Pd.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['44', 'XII TKJ 2', 'YULITA EKAWINAJENG, S.Pd.', 'CANDRA RAHADIAN, S.Pd'],
        ['45', 'XII DKV 1', 'DWI LESMIYATI, S.Pd', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['46', 'XII DKV 2', 'TITI SARI, S.Kom.', 'NURHIDAYAT SULISTIAWAN, S.Pd'],
        ['47', 'XII RPL 1', 'DIAH UTAMININGSIH, S.T', 'RYAWIDYANINGTYAS, S.Pd.'],
        ['48', 'XII RPL 2', 'ROSYIDA RAMADHANI, S.Pd', 'RYAWIDYANINGTYAS, S.Pd.'],
    ];

    private static array $teacherAliases = [
        'ahmadmuhlashinspd' => 'ahmadmuhlasinspd',
        'trisusilawatistmpd' => 'trisusilowatistmpd',
        'anastasiaratnasulistiyanispd' => 'anastyaratnasulistiyanispd',
        'hermicahyawatispdmsi' => 'hermicahyawatispdi',
        'agnesendangmudjirahayuspdmpd' => 'agnesendangmudjirahayumpd',
        'ririhdamayantiwisnugiyonosemacc' => 'ririhdamayantiwisnugiyonomacc',
        'faillarochmayantispdmpd' => 'faillarochmayantimpd',
    ];

    public function run(): void
    {
        $allTeachers = DB::table('users')->where('role_id', 2)->get();
        $allClasses = DB::table('school_classes')->get();

        $normalize = fn (string $str) => preg_replace('/[^a-z0-9]/', '', strtolower($str));

        $now = now();
        $waliInserts = [];
        $bkInserts = [];

        foreach (self::$assignments as $item) {
            $rawClass = $item[1];
            $rawWali = $item[2];
            $rawBK = $item[3];

            $cNorm = $normalize($rawClass);
            $classObj = $allClasses->first(fn ($c) => $normalize($c->name) === $cNorm);
            if (! $classObj) {
                continue;
            }

            // Match Wali
            $wNorm = $normalize($rawWali);
            if (isset(self::$teacherAliases[$wNorm])) {
                $wNorm = self::$teacherAliases[$wNorm];
            }
            $waliTeacher = $allTeachers->first(function ($t) use ($wNorm, $normalize) {
                $tNorm = $normalize($t->fullname);
                return $tNorm === $wNorm || str_starts_with($tNorm, $wNorm) || str_starts_with($wNorm, $tNorm);
            });

            // Match BK
            $bNorm = $normalize($rawBK);
            if (isset(self::$teacherAliases[$bNorm])) {
                $bNorm = self::$teacherAliases[$bNorm];
            }
            $bkTeacher = $allTeachers->first(function ($t) use ($bNorm, $normalize) {
                $tNorm = $normalize($t->fullname);
                return $tNorm === $bNorm || str_starts_with($tNorm, $bNorm) || str_starts_with($bNorm, $tNorm);
            });

            if ($waliTeacher) {
                $waliInserts[] = [
                    'user_id' => $waliTeacher->id,
                    'school_class_id' => $classObj->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                DB::table('users')->where('id', $waliTeacher->id)->update([
                    'class_id' => $classObj->id,
                ]);
            }

            if ($bkTeacher) {
                $bkInserts[] = [
                    'user_id' => $bkTeacher->id,
                    'school_class_id' => $classObj->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                DB::table('users')->where('id', $bkTeacher->id)->update([
                    'role_id' => 4,
                ]);
            }
        }

        DB::table('user_school_classes')->delete();
        DB::table('user_bk_classes')->delete();

        if (! empty($waliInserts)) {
            DB::table('user_school_classes')->insertOrIgnore($waliInserts);
        }

        if (! empty($bkInserts)) {
            DB::table('user_bk_classes')->insertOrIgnore($bkInserts);
        }
    }
}

