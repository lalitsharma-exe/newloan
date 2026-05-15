<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Section 1 & 2
            $table->string('institution_id')->default('MyLoan Limited')->after('id');
            $table->year('financial_year')->after('institution_id');
            $table->string('reporting_period')->after('financial_year');
            $table->string('reference_number')->unique()->after('reporting_period');
            
            // Section 3: Customer Details (Extending existing user/loan links)
            $table->string('customer_first_name')->after('user_id');
            $table->string('customer_surname')->after('customer_first_name');
            $table->string('account_number')->after('customer_surname');
            $table->string('customer_type')->after('account_number');
            $table->string('customer_type_other')->nullable()->after('customer_type');
            $table->string('customer_cell_number')->after('customer_type_other');
            $table->string('customer_email_address')->nullable()->after('customer_cell_number');
            $table->string('age_group')->after('customer_email_address');
            $table->string('sex')->after('age_group');
            
            // Section 4: Receipt Details
            $table->string('mode_of_receipt')->after('sex');
            $table->string('received_at_place')->after('mode_of_receipt');
            $table->string('district')->after('received_at_place');
            
            // Section 5: Classification
            $table->string('product_category')->after('district');
            $table->string('product_category_other')->nullable()->after('product_category');
            $table->string('issue_category')->after('product_category_other');
            $table->string('issue_category_other')->nullable()->after('issue_category');
            
            // Section 6: Resolution
            $table->text('status_description')->nullable()->after('status');
            $table->integer('working_days_to_resolve')->nullable()->after('resolved_date');
            $table->decimal('amount_reimbursed', 15, 2)->nullable()->after('working_days_to_resolve');
            $table->string('complainant_name_third_party')->nullable()->after('amount_reimbursed');
            
            // Cleanup old basic fields if necessary (or repurpose)
            // We'll keep 'complaint_date' and 'description' as they exist.
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'institution_id', 'financial_year', 'reporting_period', 'reference_number',
                'customer_first_name', 'customer_surname', 'account_number', 'customer_type', 
                'customer_type_other', 'customer_cell_number', 'customer_email_address', 
                'age_group', 'sex', 'mode_of_receipt', 'received_at_place', 'district', 
                'product_category', 'product_category_other', 'issue_category', 
                'issue_category_other', 'status_description', 'working_days_to_resolve', 
                'amount_reimbursed', 'complainant_name_third_party'
            ]);
        });
    }
};
