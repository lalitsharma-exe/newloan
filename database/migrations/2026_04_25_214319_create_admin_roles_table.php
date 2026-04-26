<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // e.g. "Super Admin", "Finance Manager"
            $table->string('slug')->unique();          // e.g. "super_admin", "finance_manager"
            $table->json('permissions')->nullable();    // {"dashboard":true, "loans.view":true, ...}
            $table->boolean('is_super_admin')->default(false); // Bypasses all permission checks
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add admin_role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_role_id']);
            $table->dropColumn('admin_role_id');
        });
        Schema::dropIfExists('admin_roles');
    }
};
