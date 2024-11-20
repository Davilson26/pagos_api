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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('card_number'); // Mejor enmascarado y encriptado
            $table->string('card_holder'); // Títular de la tarjeta
            $table->integer('card_type'); // Títular de la tarjeta
            $table->date('expiry_date');
            $table->string('cvv'); // Mejor enmascarado y encriptado
            $table->decimal('amount', 12, 2);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
