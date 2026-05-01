<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignId('farmer_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->decimal('amount_fcfa', 12, 2);          // montant original
            $table->decimal('remaining_fcfa', 12, 2);       // reste à payer
            $table->enum('status', ['open', 'partial', 'paid'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};