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
        Schema::create('details', function (Blueprint $table) {
            $table->id();

            // $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            // $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->string('product_code');
            $table->foreign('product_code')->references('code')->on('products')->onDelete('cascade');

            $table->double('unit_price')->nullable();
            $table->integer('quantity')->unsigned()->default(0);
            $table->integer('promotion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
