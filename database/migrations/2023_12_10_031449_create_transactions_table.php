<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type'); // DEP para depósito, TRANSF para transferência, etc.
            $table->string('authorization_code')->unique();
            $table->decimal('value', 10, 2);
            $table->unsignedBigInteger('source_account_id')->nullable(); // Adiciona campo para conta de origem
            $table->unsignedBigInteger('destination_account_id')->nullable(); // Adiciona campo para conta de destino
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
