<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('sku', 40)->unique();          // contrainte d'unicité en base
            $table->string('category', 60)->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();                          // created_at, updated_at
            $table->softDeletes();                         // deleted_at
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
