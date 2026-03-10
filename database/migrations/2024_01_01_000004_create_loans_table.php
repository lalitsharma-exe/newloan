<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create("loans",function(Blueprint $t){
            $t->id(); $t->string("loan_number",30)->unique();
            $t->foreignId("user_id")->constrained()->onDelete("cascade");
            $t->foreignId("loan_product_id")->nullable()->constrained()->nullOnDelete();
            $t->foreignId("application_id")->nullable()->constrained("loan_applications")->nullOnDelete();
            $t->decimal("principal_amount",12,2); $t->decimal("interest_rate",6,4); $t->integer("term_months");
            $t->decimal("total_amount",12,2); $t->decimal("outstanding_balance",12,2);
            $t->decimal("monthly_installment",12,2); $t->decimal("processing_fee",10,2)->default(0);
            $t->enum("status",["active","overdue","paid_off","closed","defaulted"])->default("active");
            $t->date("disbursement_date")->nullable(); $t->date("maturity_date")->nullable();
            $t->date("first_payment_date")->nullable(); $t->date("last_payment_date")->nullable();
            $t->string("payout_method",50)->nullable(); $t->string("collection_method",50)->nullable();
            $t->timestamp("closed_at")->nullable(); $t->text("closed_reason")->nullable();
            $t->foreignId("closed_by")->nullable()->constrained("users")->nullOnDelete();
            $t->softDeletes(); $t->timestamps();
        });
        Schema::create("loan_installments",function(Blueprint $t){
            $t->id(); $t->foreignId("loan_id")->constrained()->onDelete("cascade"); $t->integer("installment_number");
            $t->date("due_date"); $t->decimal("principal_amount",12,2); $t->decimal("interest_amount",12,2);
            $t->decimal("total_amount",12,2); $t->decimal("paid_amount",12,2)->default(0); $t->decimal("outstanding_amount",12,2);
            $t->decimal("late_fee",10,2)->default(0); $t->enum("status",["pending","paid","partial","overdue","waived"])->default("pending");
            $t->timestamp("paid_at")->nullable(); $t->timestamps();
        });
        Schema::create("payments",function(Blueprint $t){
            $t->id(); $t->string("payment_reference",30)->unique();
            $t->foreignId("loan_id")->constrained()->onDelete("cascade");
            $t->foreignId("installment_id")->nullable()->constrained("loan_installments")->nullOnDelete();
            $t->foreignId("user_id")->constrained()->onDelete("cascade");
            $t->decimal("amount",12,2); $t->enum("method",["card","bank_transfer","mobile_money","cash","other"]);
            $t->string("gateway_reference",100)->nullable(); $t->string("reference",100)->nullable();
            $t->enum("status",["pending","verified","rejected","failed"])->default("pending");
            $t->text("notes")->nullable(); $t->boolean("is_manual")->default(false);
            $t->foreignId("verified_by")->nullable()->constrained("users")->nullOnDelete();
            $t->timestamp("verified_at")->nullable(); $t->timestamps();
        });
    }
    public function down():void { Schema::dropIfExists("payments"); Schema::dropIfExists("loan_installments"); Schema::dropIfExists("loans"); }
};
