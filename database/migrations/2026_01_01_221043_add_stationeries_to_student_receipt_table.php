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
        Schema::table('student_receipts', function (Blueprint $table) {
            $table->decimal('stationeries', 10, 2)->default(0)->after('uniform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_receipts', function (Blueprint $table) {
            $table->dropColumn('stationeries');
        });
    }
};
