<?php

namespace Tests\Unit;

use App\Support\AcademicYear;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AcademicYearTest extends TestCase
{
    public function test_academic_year_changes_at_july(): void
    {
        try {
            Carbon::setTestNow('2026-06-30 12:00:00');
            $this->assertSame('2025/2026', AcademicYear::current());

            Carbon::setTestNow('2026-07-01 00:00:00');
            $this->assertSame('2026/2027', AcademicYear::current());
        } finally {
            Carbon::setTestNow();
        }
    }
}
