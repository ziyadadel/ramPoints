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
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('password');
            $table->string('role')->default('user');
            $table->integer('number_of_points')->default(0);
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_code_send_date')->nullable();
            $table->timestamp('date_registered')->nullable();
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
