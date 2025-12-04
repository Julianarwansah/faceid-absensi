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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->integer('late_threshold_minutes')->default(15);
            $table->integer('half_day_threshold_minutes')->default(240);
            $table->json('weekend_days')->default('["Saturday", "Sunday"]');
            $table->boolean('require_location')->default(false);
            $table->decimal('office_latitude', 10, 8)->nullable();
            $table->decimal('office_longitude', 11, 8)->nullable();
            $table->integer('location_radius_meters')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
