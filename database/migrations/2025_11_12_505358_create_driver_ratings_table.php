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
        Schema::create('driver_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');
            $table->unsignedBigInteger('trip_id');

            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->string('rated_by')->default('patient'); // patient, dispatcher, admin

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->foreign('trip_id')
                ->references('id')
                ->on('trips')
                ->onDelete('cascade');

            $table->unique(['driver_profile_id', 'trip_id']); // One rating per trip
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_ratings');
    }
};
