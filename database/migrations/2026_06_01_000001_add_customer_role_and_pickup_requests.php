<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'vendor', 'customer') NOT NULL DEFAULT 'vendor'");
        }

        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scrap_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('pickup_address');
            $table->string('location_notes')->nullable();
            $table->string('scrap_description');
            $table->decimal('estimated_weight_mt', 10, 2)->nullable();
            $table->date('preferred_pickup_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'pending_review',
                'assigned',
                'truck_dispatched',
                'completed',
                'cancelled',
            ])->default('pending_review');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();
        });

        Schema::table('collection_jobs', function (Blueprint $table) {
            $table->foreignId('pickup_request_id')
                ->nullable()
                ->after('godown_id')
                ->constrained('pickup_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('collection_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_request_id');
        });

        Schema::dropIfExists('pickup_requests');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'vendor') NOT NULL DEFAULT 'vendor'");
        }
    }
};
