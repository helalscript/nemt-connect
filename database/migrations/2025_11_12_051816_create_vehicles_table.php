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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            
            // ============ Sentry Info ============
            $table->string('sentry_vehicle_id')->nullable()->unique(); // ID from Sentry
            
            // ============ Basic Vehicle Information ============
            $table->string('vin', 17)->unique(); // Vehicle Identification Number
            $table->string('fleet_number', 50)->nullable(); // Your internal fleet number
            $table->integer('production_year'); // 2012, 2015, etc.
            $table->integer('type_id');
            // 1 = Ambulatory (regular car)
            // 2 = Wheelchair Accessible
            // 3 = Stretcher
            // 4 = Ambulance
            // 5 = Van
            $table->string('type_name')->nullable(); // Store for reference
            $table->string('make'); // Ford, Toyota, Mercedes
            $table->string('model'); // E150 Club Wagon, Sienna, Sprinter
            $table->string('color')->nullable(); // White, Black, Silver
            $table->integer('seat_number')->nullable(); // Passenger capacity
            
            // ============ Status ============
            $table->integer('status_id')->default(1);
            // 1 = Active
            // 2 = Inactive
            // 3 = Under Maintenance
            // 4 = Out of Service
            // 5 = Retired
            
            // ============ Owner Information ============
            // $table->unsignedBigInteger('owner_driver_profile_id')->nullable(); // Which driver owns/added this
            $table->enum('ownership_type', ['company', 'driver_owned'])->default('driver_owned');
            
            // ============ Vehicle Features ============
            $table->boolean('is_wheelchair_accessible')->default(false);
            $table->boolean('is_stretcher_accessible')->default(false);
            $table->boolean('has_wheelchair_lift')->default(false);
            $table->boolean('has_wheelchair_ramp')->default(false);
            $table->integer('wheelchair_capacity')->default(0);
            $table->boolean('has_oxygen')->default(false);
            $table->boolean('has_air_conditioning')->default(true);
            $table->boolean('has_gps')->default(true);
            
            // ============ Current Status ============
            $table->boolean('is_available')->default(true);
            $table->boolean('is_in_use')->default(false); // Currently on a trip
            // $table->unsignedBigInteger('current_driver_profile_id')->nullable(); // Who's using it now
            // $table->unsignedBigInteger('current_trip_id')->nullable(); // Current active trip
            
            // ============ Current Location ============
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            
            // ============ Credentials Overall Status ============
            $table->integer('credentials_status_id')->default(4);
            // 1 = All Valid
            // 2 = Pending Verification
            // 3 = Expired Documents
            // 4 = Incomplete
            $table->text('credentials_status_note')->nullable();
            
            // ============ Maintenance ============
            $table->integer('odometer_reading')->nullable(); // Current mileage
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_due')->nullable();
            $table->integer('maintenance_interval_miles')->default(5000); // Every 5000 miles
            
            // ============ Photos ============
            $table->string('photo_front')->nullable();
            $table->string('photo_back')->nullable();
            $table->string('photo_side')->nullable();
            $table->string('photo_interior')->nullable();
            
            // ============ Performance Metrics ============
            $table->integer('total_trips_completed')->default(0);
            $table->decimal('total_miles_driven', 10, 2)->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('breakdown_count')->default(0);
            
            // ============ Timestamps ============
            $table->timestamps();
            $table->softDeletes();
            
            
            // ============ Indexes ============
            $table->index('sentry_vehicle_id');
            $table->index('vin');
            $table->index('fleet_number');
            $table->index('status_id');
            $table->index('type_id');
            $table->index('is_available');
            $table->index(['is_available', 'status_id']); // Composite
            // $table->index('owner_driver_id');
            // $table->index('current_driver_id');
        });
        
        // ============ DMV Registration Table ============
        Schema::create('vehicle_dmv_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            
            $table->string('license_plate_number')->unique();
            $table->integer('license_plate_category_id')->nullable();
            // 1 = Passenger
            // 2 = Commercial
            // 3 = For-Hire Vehicle
            // 4 = Ambulance
            $table->string('state_code', 5); // NY, CA, NJ
            $table->string('document_url')->nullable(); // Registration document photo
            $table->date('effective_date')->nullable();
            $table->date('expiration_date');
            
            // Status from Sentry
            $table->integer('status_id')->default(2);
            // 1 = Active/Valid
            // 2 = Pending Verification
            // 3 = Expired
            // 4 = Suspended
            $table->text('status_note')->nullable();
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // Admin user
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            $table->index('license_plate_number');
            $table->index('expiration_date');
            $table->index('status_id');
        });
        
        // ============ Extra Commercial Licenses Table ============
        Schema::create('vehicle_extra_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            
            $table->string('license_number');
            $table->integer('type_id');
            // 1 = TLC License (NYC Taxi & Limousine Commission)
            // 2 = For-Hire Vehicle License
            // 3 = Ambulance License
            // 4 = Paratransit License
            $table->string('type_name')->nullable();
            $table->string('document_url')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiration_date');
            
            // Status
            $table->integer('status_id')->default(2);
            $table->text('status_note')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            $table->index('license_number');
            $table->index('type_id');
            $table->index('expiration_date');
        });
        
        // ============ Insurance Table ============
        Schema::create('vehicle_insurances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            
            $table->string('policy_number')->unique();
            $table->string('insurer_name'); // State Farm, Geico, etc.
            $table->string('agent_name')->nullable();
            $table->string('agent_phone')->nullable();
            $table->string('document_url')->nullable(); // Insurance card photo
            $table->date('effective_date');
            $table->date('expiration_date');
            
            // Coverage details
            $table->decimal('liability_coverage', 12, 2)->nullable(); // $1,000,000
            $table->decimal('collision_coverage', 12, 2)->nullable();
            $table->decimal('comprehensive_coverage', 12, 2)->nullable();
            
            // Status
            $table->integer('status_id')->default(2);
            // 1 = Active
            // 2 = Pending Verification
            // 3 = Expired
            // 4 = Cancelled
            $table->text('status_note')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            $table->index('policy_number');
            $table->index('expiration_date');
            $table->index('status_id');
        });
        
        // ============ Inspection Table ============
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            
            $table->string('inspection_type')->default('annual'); // annual, safety, emission
            $table->string('inspection_station')->nullable();
            $table->string('inspector_name')->nullable();
            $table->string('document_url')->nullable(); // Inspection certificate
            $table->date('effective_date')->nullable(); // Inspection date
            $table->date('expiration_date');
            
            // Status
            $table->integer('status_id')->default(2);
            // 1 = Passed/Valid
            // 2 = Pending
            // 3 = Expired
            // 4 = Failed
            $table->text('status_note')->nullable();
            
            // Inspection results
            $table->boolean('is_passed')->default(false);
            $table->json('failed_items')->nullable(); // ["Brakes", "Lights"]
            $table->text('inspector_notes')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            $table->index('expiration_date');
            $table->index('status_id');
            $table->index('inspection_type');
        });
        
        // ============ Vehicle Maintenance Records Table ============
        Schema::create('vehicle_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            
            $table->enum('maintenance_type', [
                'routine',
                'repair',
                'inspection',
                'tire_change',
                'oil_change',
                'brake_service',
                'other'
            ]);
            $table->text('description');
            $table->date('service_date');
            $table->integer('odometer_at_service')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('service_provider')->nullable(); // Mechanic/shop name
            $table->string('invoice_number')->nullable();
            $table->string('invoice_document')->nullable(); // Receipt photo
            
            $table->date('next_service_due')->nullable();
            $table->integer('next_service_mileage')->nullable();
            
            $table->unsignedBigInteger('recorded_by')->nullable(); // User/Admin who added
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            $table->index('vehicle_id');
            $table->index('service_date');
            $table->index('maintenance_type');
        });
        
        // ============ Vehicle Defects/Issues Table ============
        Schema::create('vehicle_defects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            // $table->unsignedBigInteger('reported_by_driver_profile_id')->nullable();
            
            $table->enum('severity', ['minor', 'moderate', 'severe', 'critical']);
            $table->text('description');
            $table->string('photo')->nullable();
            $table->timestamp('reported_at');
            
            $table->enum('status', ['open', 'in_progress', 'resolved', 'ignored'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
                  
            // $table->foreign('reported_by_driver_profile_id')
            //       ->references('id')
            //       ->on('driver_profiles')
            //       ->onDelete('set null');
                  
            $table->index('vehicle_id');
            $table->index('status');
            $table->index('severity');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('vehicle_defects');
        Schema::dropIfExists('vehicle_maintenance_records');
        Schema::dropIfExists('vehicle_inspections');
        Schema::dropIfExists('vehicle_insurances');
        Schema::dropIfExists('vehicle_extra_licenses');
        Schema::dropIfExists('vehicle_dmv_registrations');
        Schema::dropIfExists('vehicles');
    }

};
