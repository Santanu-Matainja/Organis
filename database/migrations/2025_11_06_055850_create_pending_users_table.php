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
        Schema::create('pending_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('bactive')->nullable();
            $table->string('status_id')->nullable();
            $table->string('role_id')->nullable();
            $table->string('otp_code')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('shop_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('trade_register_number')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_users');
    }
};
