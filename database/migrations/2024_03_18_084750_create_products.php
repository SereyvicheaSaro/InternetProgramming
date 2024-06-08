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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('pricing');
            $table->double('discount')->nullable();
            $table->string('tags')->nullable();
            $table->timestamp('promotion_date')->nullable();
            $table->string('color');
            $table->integer('rating')->default(0);
            $table->integer('purchased_number')->default(0);
            $table->string('size');
            $table->string('brand');
            $table->string('style')->nullable();
            $table->string('model')->nullable();
            $table->string('shipping')->nullable();
            $table->string('missing');
            $table->timestamps();

            $table->bigInteger('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};