<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a menu item to the ingredients it uses (FR-30). Resolves the many-to-many relationship.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_required', 10, 3);
            $table->timestamps();
            $table->unique(['menu_item_id', 'ingredient_id']);   // one row per ingredient per dish
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
    }
};
