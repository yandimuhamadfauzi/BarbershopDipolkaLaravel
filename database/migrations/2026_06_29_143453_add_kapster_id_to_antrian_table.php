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
        Schema::table('antrian', function (Blueprint $table) {
            $table->foreignId('kapster_id')->nullable()->constrained('kapsters')->nullOnDelete()->after('user_id');
            $table->time('waktu_selesai')->nullable()->after('jam_booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrian', function (Blueprint $table) {
            $table->dropForeign(['kapster_id']);
            $table->dropColumn(['kapster_id', 'waktu_selesai']);
        });
    }
};
