<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignId('operator_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->decimal('kg_received', 10, 3);
            $table->decimal('commodity_rate', 10, 2); // FCFA/kg au moment du remboursement
            $table->decimal('fcfa_value', 12, 2);     // kg * rate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};