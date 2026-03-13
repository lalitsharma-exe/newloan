<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $t) {
                $t->id();
                $t->string('report_type', 60);
                $t->string('frequency', 20);   // daily|weekly|monthly
                $t->string('email', 200);
                $t->string('format', 10)->default('csv');
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('scheduled_reports');
    }
};