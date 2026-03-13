<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('user_name', 200)->nullable();       // snapshot in case user deleted
            $t->string('action', 100);                      // e.g. loan.approved
            $t->string('module', 60)->nullable();           // loan | application | payment …
            $t->string('subject_type', 100)->nullable();    // App\Models\Loan
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->string('subject_label', 200)->nullable();   // LN-XXXX
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
            $t->index(['module','action']);
            $t->index(['subject_type','subject_id']);
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->onDelete('cascade');
            $t->string('type', 80);                         // loan_approved | payment_received …
            $t->string('title', 200);
            $t->text('body')->nullable();
            $t->string('link')->nullable();                 // href for the bell-click
            $t->string('icon', 60)->default('bell');
            $t->boolean('is_read')->default(false);
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
            $t->index(['user_id','is_read']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
    }
};