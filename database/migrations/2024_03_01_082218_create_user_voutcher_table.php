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
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->decimal('value_in_pounds', 6, 2);
            $table->timestamp('expiration_date')->useCurrent();
            $table->boolean('status')->default(0);
            $table->timestamp('sold_date')->nullable()->useCurrent()->default(null);
            $table->integer('num_of_point');
            $table->string('voutcher_plan_name')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('voutcher_plan_id')->references('id')->on('voutcher_plan')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branchs')->onDelete('set null');
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
