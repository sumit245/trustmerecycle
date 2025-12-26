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
        Schema::create('collection_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('godown_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'truck_dispatched', 'completed'])->default('pending');
            $table->json('truck_details')->nullable();
            $table->string('collection_proof_image')->nullable();
            $table->decimal('collected_amount_mt', 10, 2)->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_jobs');
    }
};

