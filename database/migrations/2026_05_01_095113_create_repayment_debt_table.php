<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repayment_debt', function (Blueprint $table) {
            $table->foreignId('repayment_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('debt_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->decimal('amount_applied', 12, 2); // montant appliqué sur cette dette
            $table->primary(['repayment_id', 'debt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayment_debt');
    }
};