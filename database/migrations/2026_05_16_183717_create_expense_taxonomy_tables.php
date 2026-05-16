<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expense Categories (e.g. Staff Expenses)
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ref_code')->nullable(); // e.g. "1"
            $table->timestamps();
        });

        // 2. Expense Subcategories (e.g. Salaries & Wages)
        Schema::create('expense_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('ref_code')->nullable(); // e.g. "1.1"
            $table->timestamps();
        });

        // 3. Expense Taxonomy Items (The specific descriptions like "Basic monthly salaries")
        Schema::create('expense_taxonomy_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained('expense_subcategories')->onDelete('cascade');
            $table->string('name');
            $table->string('ref_code')->nullable(); // e.g. "1.1.1"
            $table->timestamps();
        });

        // 4. Update Operating Expenses to link to Taxonomy
        Schema::table('operating_expenses', function (Blueprint $table) {
            $table->foreignId('taxonomy_item_id')->nullable()->after('uuid')->constrained('expense_taxonomy_items')->onDelete('set null');
            $table->string('category')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->foreignId('recorded_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('operating_expenses', function (Blueprint $table) {
            $table->dropForeign(['taxonomy_item_id']);
            $table->dropColumn('taxonomy_item_id');
            $table->dropForeign(['recorded_by']);
            $table->dropColumn('recorded_by');
        });

        Schema::dropIfExists('expense_taxonomy_items');
        Schema::dropIfExists('expense_subcategories');
        Schema::dropIfExists('expense_categories');
    }
};
