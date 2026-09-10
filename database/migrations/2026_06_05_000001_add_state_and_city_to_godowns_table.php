<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('godowns', function (Blueprint $table) {
            $table->string('state')->nullable()->after('location');
            $table->string('city')->nullable()->after('state');
            $table->index(['state', 'city']);
        });

        DB::table('godowns')
            ->where(function ($query) {
                $query->where('name', 'like', '%Sant Nagar%')
                    ->orWhere('address', 'like', '%Delhi%')
                    ->orWhere('location', 'like', '%Delhi%');
            })
            ->update(['state' => 'Delhi', 'city' => 'New Delhi']);
    }

    public function down(): void
    {
        Schema::table('godowns', function (Blueprint $table) {
            $table->dropIndex(['state', 'city']);
            $table->dropColumn(['state', 'city']);
        });
    }
};
