<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('customer_name');
            $table->date('quotation_date');
            $table->json('hidden_columns')->nullable();
            $table->json('custom_columns')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->decimal('vat_rate', 5, 2)->default(5);
            $table->decimal('discount', 5, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('bonus')->default(0);
            $table->json('custom_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
