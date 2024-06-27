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
        Schema::create('orders', function (Blueprint $table) {
            
            $table->id();
            $table->integer('receipt_number')->unsigned()->nullable();

            // ===== this is not yet create the cashier_id 
            // $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');

            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->double('total_price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
