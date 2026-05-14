<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to loans table
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'business_employee_count')) {
                $table->integer('business_employee_count')->nullable()->comment('Section 3.5 — SME classification');
            }
            if (!Schema::hasColumn('loans', 'borrower_monthly_income')) {
                $table->decimal('borrower_monthly_income', 14, 2)->nullable()->comment('Section 3.11 — DTI calculation');
            }
            if (!Schema::hasColumn('loans', 'borrower_total_obligations')) {
                $table->decimal('borrower_total_obligations', 14, 2)->nullable()->comment('Section 3.11 — DTI calculation');
            }
        });

        // 2. Add fields to users table (customers)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->nullable()->comment('Section 3.4 — Gender Distribution');
            }
            if (!Schema::hasColumn('users', 'first_loan_date')) {
                $table->date('first_loan_date')->nullable()->comment('Section 3.13 — new vs repeat clients');
            }
        });

        // 3. New table: sme_size_categories
        if (!Schema::hasTable('sme_size_categories')) {
            Schema::create('sme_size_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_name');
                $table->integer('min_employees');
                $table->integer('max_employees');
                $table->timestamps();
            });
        }

        // 4. New table: complaints
        if (!Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('cascade');
                $table->date('complaint_date');
                $table->string('complaint_type');
                $table->text('description');
                $table->text('resolution')->nullable();
                $table->enum('status', ['OPEN', 'RESOLVED_INTERNALLY', 'REFERRED_TO_CBL'])->default('OPEN');
                $table->string('cbl_reference', 50)->nullable();
                $table->date('resolved_date')->nullable();
                $table->timestamps();
            });
        }

        // 5. New table: loan_rate_schedule
        if (!Schema::hasTable('loan_rate_schedule')) {
            Schema::create('loan_rate_schedule', function (Blueprint $table) {
                $table->id();
                $table->string('rate_name');
                $table->decimal('interest_rate', 6, 4);
                $table->date('effective_date');
                $table->date('expiry_date')->nullable();
                $table->foreignId('approved_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 6. New table: cbl_report_archive
        if (!Schema::hasTable('cbl_report_archive')) {
            Schema::create('cbl_report_archive', function (Blueprint $table) {
                $table->id();
                $table->string('report_period'); // e.g. "Monthly - May 2026"
                $table->json('payload'); // Stores the full structured data
                $table->string('file_path_excel')->nullable();
                $table->string('file_path_pdf')->nullable();
                $table->foreignId('generated_by')->constrained('users');
                $table->timestamp('generated_at');
                $table->string('data_hash');
                $table->timestamps();
            });
        }

        // 7. New table: dpd_snapshot
        if (!Schema::hasTable('dpd_snapshot')) {
            Schema::create('dpd_snapshot', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
                $table->date('snapshot_date');
                $table->integer('dpd')->default(0);
                $table->string('aging_bucket');
                $table->timestamps();

                $table->index(['snapshot_date', 'loan_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dpd_snapshot');
        Schema::dropIfExists('cbl_report_archive');
        Schema::dropIfExists('loan_rate_schedule');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('sme_size_categories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'first_loan_date']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['business_employee_count', 'borrower_monthly_income', 'borrower_total_obligations']);
        });
    }
};
