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
            $table->decimal('external_money', 15, 2)->default(0)->after('amount_paid')->comment('Any additional external payment amount');
            $table->decimal('previous_balance', 15, 2)->default(0)->after('external_money')->comment('Previous outstanding balance of the student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_receipts', function (Blueprint $table) {
            $table->dropColumn(['external_money', 'previous_balance']);
        });
    }
};
