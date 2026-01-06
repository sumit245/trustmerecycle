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
        Schema::table('collection_jobs', function (Blueprint $table) {
            $table->string('challan_image')->nullable()->after('collection_proof_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_jobs', function (Blueprint $table) {
            $table->dropColumn('challan_image');
        });
    }
};
