<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'approved' and 'written_off' to the loans status enum
        DB::statement("ALTER TABLE `loans` MODIFY COLUMN `status` ENUM(
            'approved',
            'active',
            'overdue',
            'paid_off',
            'closed',
            'defaulted',
            'written_off'
        ) NOT NULL DEFAULT 'approved'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `loans` MODIFY COLUMN `status` ENUM(
            'active',
            'overdue',
            'paid_off',
            'closed',
            'defaulted'
        ) NOT NULL DEFAULT 'active'");
    }
};
