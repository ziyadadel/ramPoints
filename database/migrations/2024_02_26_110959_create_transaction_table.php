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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_qr_code', 300)->unique();
            $table->timestamp('transaction_date')->useCurrent();
            $table->integer('transaction_number');
            $table->unsignedBigInteger('branch_id');
            $table->integer('number_of_points')->default(0);
            $table->timestamp('record_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branchs')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
