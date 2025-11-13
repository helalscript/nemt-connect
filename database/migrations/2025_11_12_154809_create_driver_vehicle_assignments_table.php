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
        Schema::create('driver_vehicle_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            $table->boolean('is_primary')->default(false); // Is this the driver's main vehicle
            $table->boolean('is_active')->default(true); // Currently assigned

            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who assigned

            // Shift-based assignment
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();

            $table->text('assignment_notes')->nullable();

            $table->timestamps();


            // $table->index(['driver_profile_id', 'vehicle_id']);
            $table->index('is_active');
            $table->index(['driver_profile_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_vehicle_assignments');
    }
};
