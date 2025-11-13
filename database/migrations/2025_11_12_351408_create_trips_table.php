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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            
            // ============ Basic Trip Info ============
            $table->string('sentry_trip_id')->unique(); // STY-1000001-A
            $table->string('is_copy_of')->nullable(); // Recurring trip reference
            $table->date('date'); // Trip date
            $table->integer('reason_id')->nullable(); // 3
            $table->string('reason_name')->nullable(); // Specialist, Dialysis, etc.
            $table->string('service_level_code', 10)->nullable(); // LIV, AMB
            $table->string('assignment_type_code', 10)->nullable(); // BT, MP
            
            // ============ Vehicle Requirements ============
            $table->string('vehicle_equipment_codes', 50)->nullable(); // WC, STR, OXY
            $table->boolean('is_low_vehicle_required')->default(false);
            $table->integer('required_wc_seat_count')->default(0);
            
            // ============ Ordering Provider ============
            $table->string('ordering_provider_name')->nullable(); // First Insurance
            $table->string('ordering_provider_npi', 20)->nullable(); // 1234567890
            
            // ============ Patient/Client Info ============
            $table->string('client_id', 50)->nullable(); // CLIENT001
            $table->integer('insurance_provider_id')->nullable();
            $table->string('patient_first_name');
            $table->string('patient_last_name');
            $table->enum('patient_gender', ['M', 'F', 'O'])->nullable();
            $table->date('patient_birth_date')->nullable();
            $table->string('patient_county_code', 20)->nullable();
            
            // ============ Escort Information ============
            $table->integer('personal_care_attendant_count')->default(0);
            $table->integer('escort_adult_count')->default(0);
            $table->integer('escort_child_count')->default(0);
            $table->integer('child_seat_count')->default(0);
            $table->integer('service_animal_count')->default(0);
            
            // ============ Pickup Location ============
            $table->string('pickup_location_name')->nullable(); // Residence, Hospital
            $table->text('pickup_address');
            $table->string('pickup_building')->nullable();
            $table->string('pickup_apartment')->nullable();
            $table->string('pickup_city');
            $table->string('pickup_county_code', 20)->nullable();
            $table->string('pickup_state_code', 5);
            $table->string('pickup_zip_code', 10);
            $table->string('pickup_phone', 20)->nullable();
            $table->string('pickup_phone_ext', 10)->nullable();
            $table->string('pickup_alternate_phone', 20)->nullable();
            $table->string('pickup_alternate_phone_ext', 10)->nullable();
            $table->text('pickup_directions')->nullable(); // Important instructions
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            
            // ============ Drop-off Location ============
            $table->string('dropoff_location_name')->nullable();
            $table->text('dropoff_address');
            $table->string('dropoff_building')->nullable();
            $table->string('dropoff_apartment')->nullable();
            $table->string('dropoff_city');
            $table->string('dropoff_county_code', 20)->nullable();
            $table->string('dropoff_state_code', 5);
            $table->string('dropoff_zip_code', 10);
            $table->text('dropoff_directions')->nullable();
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();
            
            // ============ Facility Contact ============
            $table->string('facility_phone', 20)->nullable();
            $table->string('facility_phone_ext', 10)->nullable();
            
            // ============ Timing ============
            $table->timestamp('scheduled_pickup_time'); // 2022-10-01 10:00:00
            $table->integer('scheduled_pickup_source_id')->nullable(); // 1=Appointment, 2=Will call
            $table->timestamp('latest_pickup_time')->nullable(); // Must pickup by this time
            $table->timestamp('scheduled_dropoff_time')->nullable();
            $table->integer('scheduled_dropoff_source_id')->nullable();
            
            // ============ Actual Trip Times (Driver fills these) ============
            $table->timestamp('pickup_arrival_time')->nullable(); // Driver reached pickup location
            $table->timestamp('actual_pickup_time')->nullable(); // Patient boarded
            $table->timestamp('actual_dropoff_time')->nullable(); // Patient dropped
            
            // ============ Distance & Route ============
            $table->decimal('estimated_mileage', 8, 2)->nullable(); // 5.1 miles
            $table->decimal('actual_mileage', 8, 2)->nullable(); // Actual driven
            $table->boolean('is_congestion_surcharge_route')->default(false);
            
            // ============ Pricing from Sentry ============
            $table->decimal('sentry_gross_cost', 10, 2)->nullable();
            $table->decimal('sentry_initial_cost', 10, 2)->nullable();
            $table->decimal('sentry_procedures_cost', 10, 2)->nullable();
            $table->decimal('sentry_pickup_arrival_cost', 10, 2)->nullable();
            $table->decimal('sentry_delivery_cost', 10, 2)->nullable();
            $table->decimal('sentry_driver_incentives', 10, 2)->nullable();
            $table->decimal('sentry_adjustment', 10, 2)->default(0);
            $table->text('sentry_adjustment_note')->nullable();
            $table->decimal('sentry_actual_cost', 10, 2)->nullable(); // Total from Sentry
            $table->decimal('sentry_fare', 10, 2)->default(0); // Patient payment
            
            // ============ YOUR CUSTOM PRICE ============
            $table->decimal('custom_price', 10, 2)->nullable(); // তোমার set করা price
            $table->text('custom_price_note')->nullable(); // কেন change করলে
            $table->timestamp('custom_price_set_at')->nullable();
            $table->unsignedBigInteger('custom_price_set_by')->nullable(); // Admin user ID
            
            // ============ Driver Assignment ============
            $table->unsignedBigInteger('driver_profile_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable(); // Admin who assigned
            
            // ============ Trip Status ============
            $table->enum('status', [
                'pending_assignment',  // Just received, not assigned yet
                'assigned',            // Assigned to driver
                'accepted',            // Driver accepted
                'rejected_by_driver',  // Driver rejected
                'en_route_to_pickup',  // Going to pickup
                'arrived_at_pickup',   // Reached pickup location
                'patient_on_board',    // Patient picked up
                'en_route_to_dropoff', // Going to destination
                'arrived_at_dropoff',  // Reached destination
                'completed',           // Trip finished
                'cancelled',           // Cancelled
                'no_show'              // Patient didn't show up
            ])->default('pending_assignment');
            
            $table->integer('sentry_status_id')->nullable(); // Sentry's status ID
            $table->integer('acceptance_status_id')->default(0); // 0=pending, 1=accepted, 2=rejected
            $table->boolean('is_confirmed')->default(false); // Patient confirmed
            
            // ============ Cancellation ============
            $table->integer('cancel_reason_id')->nullable();
            $table->text('cancel_note')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // ============ Rejection (by driver) ============
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // ============ Special Requirements & Notes ============
            $table->text('comments')->nullable(); // From broker/hospital
            $table->text('notes_from_provider')->nullable(); // Your notes
            $table->text('driver_notes')->nullable(); // Driver's notes
            $table->boolean('is_covid_transport')->default(false);
            
            // ============ Billing ============
            $table->integer('billing_status_id')->default(0); // 0=Not Attested, 1=Submitted
            $table->string('billing_status_name')->nullable();
            $table->text('billing_note')->nullable();
            $table->decimal('payable_amount', 10, 2)->nullable();
            $table->decimal('payable_driver_incentives', 10, 2)->nullable();
            
            // ============ Procedures (JSON field for billing codes) ============
            $table->json('procedures')->nullable(); // Store procedures array
            
            // ============ Tracking ============
            $table->timestamp('last_modified_at')->nullable(); // From Sentry
            $table->boolean('is_done_by_not_integrated_provider')->default(false);
            
            // ============ Signatures (for completion) ============
            $table->text('patient_signature')->nullable(); // Base64 image
            $table->text('driver_signature')->nullable(); // Base64 image
            $table->timestamp('signature_captured_at')->nullable();
            
            // ============ Laravel Timestamps ============
            $table->timestamps();
            $table->softDeletes(); // For soft delete if needed
            
            // ============ Foreign Keys ============
            $table->foreign('driver_profile_id')->references('id')->on('driver_profiles')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('custom_price_set_by')->references('id')->on('users')->onDelete('set null');
            
            // ============ Indexes for Performance ============
            $table->index('sentry_trip_id');
            $table->index('date');
            $table->index('status');
            $table->index('driver_profile_id');
            $table->index('vehicle_id');
            $table->index(['date', 'status']); // Composite index
            $table->index('scheduled_pickup_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
