<?php

namespace App\Support;

class AcademicYear
{
    public static function current(): string
    {
        $year = now()->month >= 7 ? now()->year : now()->year - 1;

        return $year.'/'.($year + 1);
    }
}
