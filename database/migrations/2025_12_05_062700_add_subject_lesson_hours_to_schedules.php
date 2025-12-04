<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('day');
            $table->integer('lesson_hours')->default(1)->after('end_time'); // 1 jam = 45 menit
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['subject', 'lesson_hours']);
        });
    }
};
