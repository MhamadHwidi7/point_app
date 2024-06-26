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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->double('total_points')->default(0.0);
            $table->string('account_number')->unique(); // Ensure uniqueness for account numbers
            $table->string('card_number')->unique(); // Add card number field
            $table->string('card_passcode'); // Add card passcode field
            $table->string('device_token')->nullable();
            $table->string('verification_code')->nullable(); // Add verification_code field
            $table->double('total_money')->default(0.0);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
