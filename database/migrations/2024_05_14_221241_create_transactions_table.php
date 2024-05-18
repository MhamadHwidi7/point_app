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
            $table->string('sender_account_number');
            $table->string('receiver_account_number');
            $table->string('sender_card_number');
            $table->decimal('amount', 15, 2);
            $table->string('purpose');
            $table->decimal('fee', 15, 2);
            $table->string('reference_number')->unique();
            $table->string('rajhi_benefits')->nullable();

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
