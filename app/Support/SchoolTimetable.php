<?php

namespace App\Support;

class SchoolTimetable
{
    public const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    private const PERIODS = [
        'Senin' => [
            'upacara' => ['label' => 'Upacara / Briefing', 'start' => '07:00', 'end' => '08:00'],
            'period_1' => ['label' => 'Jam ke-1', 'start' => '08:00', 'end' => '08:35'],
            'period_2' => ['label' => 'Jam ke-2', 'start' => '08:35', 'end' => '09:10'],
            'period_3' => ['label' => 'Jam ke-3', 'start' => '09:10', 'end' => '09:45'],
            'period_4' => ['label' => 'Jam ke-4', 'start' => '10:00', 'end' => '10:35'],
            'period_5' => ['label' => 'Jam ke-5', 'start' => '10:35', 'end' => '11:10'],
            'period_6' => ['label' => 'Jam ke-6', 'start' => '11:10', 'end' => '11:45'],
            'period_7' => ['label' => 'Jam ke-7', 'start' => '12:45', 'end' => '13:25'],
            'period_8' => ['label' => 'Jam ke-8', 'start' => '13:25', 'end' => '14:00'],
            'period_9' => ['label' => 'Jam ke-9', 'start' => '14:00', 'end' => '14:35'],
            'period_10' => ['label' => 'Jam ke-10', 'start' => '14:35', 'end' => '15:10'],
            'piket' => ['label' => 'Shalat Ashar / Piket Kelas', 'start' => '15:10', 'end' => '15:30'],
        ],
        'Selasa' => [
            'literasi' => ['label' => 'Literasi', 'start' => '07:00', 'end' => '07:30'],
            'period_1' => ['label' => 'Jam ke-1', 'start' => '07:30', 'end' => '08:10'],
            'period_2' => ['label' => 'Jam ke-2', 'start' => '08:10', 'end' => '08:50'],
            'period_3' => ['label' => 'Jam ke-3', 'start' => '08:50', 'end' => '09:30'],
            'period_4' => ['label' => 'Jam ke-4', 'start' => '09:45', 'end' => '10:25'],
            'period_5' => ['label' => 'Jam ke-5', 'start' => '10:25', 'end' => '11:05'],
            'period_6' => ['label' => 'Jam ke-6', 'start' => '11:05', 'end' => '11:45'],
            'period_7' => ['label' => 'Jam ke-7', 'start' => '12:45', 'end' => '13:25'],
            'period_8' => ['label' => 'Jam ke-8', 'start' => '13:25', 'end' => '14:00'],
            'period_9' => ['label' => 'Jam ke-9', 'start' => '14:00', 'end' => '14:35'],
            'period_10' => ['label' => 'Jam ke-10', 'start' => '14:35', 'end' => '15:10'],
            'piket' => ['label' => 'Shalat Ashar / Piket Kelas', 'start' => '15:10', 'end' => '15:30'],
        ],
        'Rabu' => [
            'period_1' => ['label' => 'Jam ke-1', 'start' => '07:15', 'end' => '07:55'],
            'period_2' => ['label' => 'Jam ke-2', 'start' => '07:55', 'end' => '08:35'],
            'period_3' => ['label' => 'Jam ke-3', 'start' => '08:35', 'end' => '09:15'],
            'period_4' => ['label' => 'Jam ke-4', 'start' => '09:30', 'end' => '10:10'],
            'period_5' => ['label' => 'Jam ke-5', 'start' => '10:10', 'end' => '10:50'],
            'period_6' => ['label' => 'Jam ke-6', 'start' => '10:50', 'end' => '11:30'],
            'period_7' => ['label' => 'Jam ke-7', 'start' => '12:30', 'end' => '13:10'],
            'period_8' => ['label' => 'Jam ke-8', 'start' => '13:10', 'end' => '13:50'],
            'period_9' => ['label' => 'Jam ke-9', 'start' => '13:50', 'end' => '14:30'],
            'period_10' => ['label' => 'Jam ke-10', 'start' => '14:30', 'end' => '15:10'],
            'piket' => ['label' => 'Shalat Ashar / Piket Kelas', 'start' => '15:10', 'end' => '15:30'],
        ],
        'Kamis' => [
            'period_1' => ['label' => 'Jam ke-1', 'start' => '07:15', 'end' => '07:55'],
            'period_2' => ['label' => 'Jam ke-2', 'start' => '07:55', 'end' => '08:35'],
            'period_3' => ['label' => 'Jam ke-3', 'start' => '08:35', 'end' => '09:15'],
            'period_4' => ['label' => 'Jam ke-4', 'start' => '09:30', 'end' => '10:10'],
            'period_5' => ['label' => 'Jam ke-5', 'start' => '10:10', 'end' => '10:50'],
            'period_6' => ['label' => 'Jam ke-6', 'start' => '10:50', 'end' => '11:30'],
            'period_7' => ['label' => 'Jam ke-7', 'start' => '12:30', 'end' => '13:10'],
            'period_8' => ['label' => 'Jam ke-8', 'start' => '13:10', 'end' => '13:50'],
            'period_9' => ['label' => 'Jam ke-9', 'start' => '13:50', 'end' => '14:30'],
            'period_10' => ['label' => 'Jam ke-10', 'start' => '14:30', 'end' => '15:10'],
            'piket' => ['label' => 'Shalat Ashar / Piket Kelas', 'start' => '15:10', 'end' => '15:30'],
        ],
        'Jumat' => [
            'period_1' => ['label' => 'Jam ke-1', 'start' => '07:15', 'end' => '07:55'],
            'period_2' => ['label' => 'Jam ke-2', 'start' => '07:55', 'end' => '08:35'],
            'period_3' => ['label' => 'Jam ke-3', 'start' => '08:35', 'end' => '09:15'],
            'period_4' => ['label' => 'Jam ke-4', 'start' => '09:30', 'end' => '10:10'],
            'period_5' => ['label' => 'Jam ke-5', 'start' => '10:10', 'end' => '10:50'],
            'period_6' => ['label' => 'Jam ke-6', 'start' => '10:50', 'end' => '11:25'],
            'period_7' => ['label' => 'Jam ke-7', 'start' => '12:40', 'end' => '13:15'],
            'period_8' => ['label' => 'Jam ke-8', 'start' => '13:15', 'end' => '13:50'],
            'piket' => ['label' => 'Piket Kelas', 'start' => '13:50', 'end' => '14:00'],
        ],
    ];

    public static function periodsForDay(string $day): array
    {
        return self::PERIODS[$day] ?? [];
    }

    public static function periodFor(string $day, string $code): ?array
    {
        return self::periodsForDay($day)[$code] ?? null;
    }

    public static function codeFor(string $day, string $start, string $end): ?string
    {
        foreach (self::periodsForDay($day) as $code => $period) {
            if ($period['start'] === substr($start, 0, 5) && $period['end'] === substr($end, 0, 5)) {
                return $code;
            }
        }

        return null;
    }

    public static function labelFor(string $day, string $start, string $end): ?string
    {
        $code = self::codeFor($day, $start, $end);
        if ($code !== null) {
            return self::periodFor($day, $code)['label'];
        }

        $startLabel = null;
        $endLabel = null;
        foreach (self::periodsForDay($day) as $period) {
            if ($period['start'] === substr($start, 0, 5)) {
                $startLabel = $period['label'];
            }
            if ($period['end'] === substr($end, 0, 5)) {
                $endLabel = $period['label'];
            }
        }

        if ($startLabel !== null && $endLabel !== null) {
            return $startLabel === $endLabel ? $startLabel : $startLabel . ' sampai ' . $endLabel;
        }

        return null;
    }
}
