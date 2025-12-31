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
        // ============ Driver Profiles Table ============
        Schema::create('driver_profiles', function (Blueprint $table) { 
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            // ============ Sentry Info ============
            $table->unsignedBigInteger('sentry_driver_id')->nullable()->unique(); // ID from Sentry

            // // ============ Basic Information ============
            $table->string('first_name');
            $table->string('last_name');
            $table->string('social_security_number', 4)->nullable(); // Last 4 digits only
            $table->enum('gender', ['M', 'F', 'O'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20);
        

            // ============ Status ============
            $table->integer('status_id')->default(1); // 1 = Active, 2 = Inactive, 3 = Suspended,  4 = Pending Approval

            // ============ Online/Offline Status ============
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(true); // Available for new trips
            $table->timestamp('last_active_at')->nullable();

            // ============ Current Location ============
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->decimal('current_speed', 5, 2)->nullable(); // km/h
            $table->string('current_heading')->nullable(); // N, NE, E, SE, S, SW, W, NW
            $table->timestamp('location_updated_at')->nullable();
            // ============ Working Area ============
            $table->string('working_area_name')->nullable(); // Working area name
            $table->decimal('working_area_lat', 10, 7)->nullable();
            $table->decimal('working_area_lng', 10, 7)->nullable();
            $table->decimal('working_area_radius', 10, 2)->nullable(); // in miles
            
            // ============ Current Vehicle Assignment ============
            $table->foreignId('current_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');

            // ============ Credentials Overall Status ============
            $table->integer('credentials_status_id')->default(2); // 1 = Approved, 2 = Pending Verification, 3 = Rejected, 4 = Incomplete
            
            $table->text('credentials_status_note')->nullable();

            // ============ MTA Approval ============
            $table->boolean('is_approved_for_mta')->default(false);

            // ============ Emergency Contact ============
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // ============ Banking Info (for payments) ============
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable(); // Encrypted
            $table->string('bank_routing_number')->nullable(); // Encrypted
             
            // ============ ADMIN VERIFIED ============
            $table->boolean('is_admin_verified')->default(false);
            $table->timestamp('admin_verified_at')->nullable();
            $table->foreignId('admin_verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_verified_note')->nullable();
            
            // ============ ADMIN REJECTED ============
            $table->boolean('is_admin_rejected')->default(false);
            $table->timestamp('admin_rejected_at')->nullable();
            $table->foreignId('admin_rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_rejected_reason')->nullable();
            // ============ Timestamps ============
            $table->timestamps();
            $table->softDeletes();

            // ============ Indexes ============
            $table->index('user_id');
            $table->index('sentry_driver_id');
            $table->index('is_online');
            $table->index('is_available');
            $table->index('status_id');
            $table->index(['is_online', 'is_available']); // Composite index
        });

        // ============ DMV License Table ============
        Schema::create('driver_dmv_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->string('license_number')->unique();
            $table->string('state_code', 5); // NY, CA, etc.
            $table->string('license_class')->nullable(); // A, B, C, D
            $table->json('endorsements')->nullable(); // ["G", "P", "N"]
            $table->json('restrictions')->nullable(); // ["A2", "B"]
            $table->string('document_url')->nullable(); // Photo of license
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();

            // Status from Sentry
            $table->integer('status_id')->default(2);
            // 1 = Verified/Active
            // 2 = Pending Verification
            // 3 = Expired
            // 4 = Suspended
            // 5 = Rejected
            $table->text('status_note')->nullable();

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // Admin user

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index('license_number');
            $table->index('expiration_date');
            $table->index('status_id');
        });

        // ============ Extra Commercial Licenses Table ============
        Schema::create('driver_extra_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->string('license_number');
            $table->integer('type_id');
            // 1 = TLC License (NYC Taxi & Limousine Commission)
            // 2 = CDL (Commercial Driver's License)
            // 3 = For-Hire Vehicle License
            // 4 = Ambulance Driver Certificate
            $table->string('type_name')->nullable(); // Stored for reference
            $table->string('document_url')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();

            // Status
            $table->integer('status_id')->default(2);
            $table->text('status_note')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index('license_number');
            $table->index('type_id');
            $table->index('expiration_date');
        });

        // ============ Background Check Table ============
        Schema::create('driver_background_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->string('document_url')->nullable(); // Background check report
            $table->date('effective_date')->nullable(); // When check was done
            $table->date('expiration_date'); // Needs renewal

            // Status
            $table->integer('status_id')->default(2);
            // 1 = Cleared/Approved
            // 2 = Pending
            // 3 = Expired
            // 4 = Failed/Rejected
            $table->text('status_note')->nullable();

            // Additional fields
            $table->string('check_provider')->nullable(); // Company that did the check
            $table->string('confirmation_number')->nullable();
            $table->boolean('is_cleared')->default(false);

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index('expiration_date');
            $table->index('status_id');
        });

        // ============ MTA Drug Screen Table ============
        Schema::create('driver_mta_drug_screens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->string('document_url')->nullable();
            $table->date('result_verification_date')->nullable(); // Test date
            $table->date('expiration_date')->nullable();

            // Status
            $table->integer('status_id')->default(2);
            // 1 = Passed
            // 2 = Pending
            // 3 = Expired
            // 4 = Failed
            $table->text('status_note')->nullable();

            // Additional info
            $table->string('test_facility')->nullable();
            $table->string('confirmation_number')->nullable();
            $table->boolean('is_passed')->default(false);

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index('expiration_date');
            $table->index('status_id');
        });

        // ============ Driver Certifications Table ============
        Schema::create('driver_certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->string('certification_type');
            // CPR, First Aid, Defensive Driving, Wheelchair Training, etc.
            $table->string('certification_number')->nullable();
            $table->string('issuing_organization')->nullable();
            $table->string('document_url')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiration_date')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index('certification_type');
            $table->index('expiration_date');
        });

        // ============ Driver Shift Schedule Table ============
        Schema::create('driver_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_profile_id');

            $table->date('shift_date');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);

            $table->boolean('is_recurring')->default(false); // Weekly recurring shift
            $table->boolean('is_active')->default(true);

            // Actual clock in/out
            $table->timestamp('actual_clock_in')->nullable();
            $table->timestamp('actual_clock_out')->nullable();

            $table->timestamps();

            $table->foreign('driver_profile_id')
                ->references('id')
                ->on('driver_profiles')
                ->onDelete('cascade');

            $table->index(['driver_profile_id', 'shift_date']);
            $table->index('shift_date');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_shifts');
        Schema::dropIfExists('driver_certifications');
        Schema::dropIfExists('driver_mta_drug_screens');
        Schema::dropIfExists('driver_background_checks');
        Schema::dropIfExists('driver_extra_licenses');
        Schema::dropIfExists('driver_dmv_licenses');
        Schema::dropIfExists('driver_profiles');
    }
};
