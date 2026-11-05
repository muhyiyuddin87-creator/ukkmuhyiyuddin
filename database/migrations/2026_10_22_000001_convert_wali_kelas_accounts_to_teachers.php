<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role_id', 5)->update(['role_id' => 2]);
    }

    public function down(): void
    {
        // The original role cannot be restored without also changing regular teacher accounts.
    }
};
