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
        $table->uuid('id')->primary();
        $table->string('code')->unique();
        $table->string('name', 120);
        $table->enum('category', ['electronics','clothing','food','otros']);
        $table->decimal('price', 10, 2);
        $table->unsignedInteger('stock');
        $table->boolean('is_active')->default(true);
        $table->string('image_url')->nullable();
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
