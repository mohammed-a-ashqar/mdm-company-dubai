<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            // Set when the line's paid value is spread over paid + free units; null keeps the standard paid-only pricing.
            $table->decimal('averaged_unit_price', 18, 8)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->dropColumn('averaged_unit_price');
        });
    }
};
