<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geofence_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained()->onDelete('set null');
            $table->string('class_name');
            $table->decimal('teacher_lat', 10, 7);
            $table->decimal('teacher_lng', 10, 7);
            $table->decimal('class_lat', 10, 7);
            $table->decimal('class_lng', 10, 7);
            $table->decimal('distance', 10, 2); // dalam meter
            $table->decimal('radius', 10, 2); // radius yang dibolehkan
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofence_violations');
    }
};
