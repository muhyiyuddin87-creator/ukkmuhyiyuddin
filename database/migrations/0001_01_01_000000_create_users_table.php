<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id');
            $table->integer('parent_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->string('username', 50);
            $table->string('password', 255);
            $table->string('fullname', 100);
            $table->timestamp('created_at')->useCurrent();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
