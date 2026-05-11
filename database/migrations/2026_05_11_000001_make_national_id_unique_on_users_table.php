<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, let's check for any existing duplicates and handle them
        // We'll keep the oldest record and nullify the national_id for others
        // to avoid migration failure.
        $duplicates = DB::table('users')
            ->select('national_id')
            ->whereNotNull('national_id')
            ->groupBy('national_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->pluck('national_id');

        foreach ($duplicates as $id) {
            $ids = DB::table('users')
                ->where('national_id', $id)
                ->orderBy('created_at', 'asc')
                ->pluck('id');

            // Keep the first one, nullify others
            if ($ids->count() > 1) {
                $ids->shift(); // Remove the first one from the list of to-be-nullified
                DB::table('users')->whereIn('id', $ids)->update(['national_id' => null]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('national_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['national_id']);
        });
    }
};
