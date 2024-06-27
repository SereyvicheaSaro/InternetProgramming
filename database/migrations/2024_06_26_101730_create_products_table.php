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

            $table->unsignedBigInteger('category_id'); // create new column 
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade'); // ref to the column

            $table->string('code')->unique();
            $table->string('name', 30);
            $table->decimal('unit_price', 8, 2);
            $table->integer('quantity');
            $table->text('image')->nullable();
            $table->integer('promotion');
            $table->integer('alert');
            $table->timestamps();
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
