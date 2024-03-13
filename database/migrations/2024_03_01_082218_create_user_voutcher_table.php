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
        Schema::create('user_voutcher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('voutcher_plan_id');
            $table->decimal('value_in_pounds', 6, 2);
            $table->timestamp('expiration_date');
            $table->boolean('status')->default(1);
            $table->timestamp('sold_date')->nullable();
            $table->string('num_of_point');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('voutcher_plan_id')->references('id')->on('voutcher_plan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_voutcher');
    }
};
