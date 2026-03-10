<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create("documents",function(Blueprint $t){
            $t->id(); $t->foreignId("user_id")->constrained()->onDelete("cascade");
            $t->foreignId("application_id")->nullable()->constrained("loan_applications")->nullOnDelete();
            $t->enum("type",["national_id","payslip","bank_statement","photo","other"]);
            $t->string("filename"); $t->string("original_name"); $t->string("mime_type",100)->nullable(); $t->unsignedBigInteger("size")->nullable(); $t->string("path");
            $t->enum("status",["pending","verified","rejected"])->default("pending");
            $t->foreignId("verified_by")->nullable()->constrained("users")->nullOnDelete();
            $t->timestamp("verified_at")->nullable(); $t->text("notes")->nullable(); $t->timestamps();
        });
        Schema::create("application_notes",function(Blueprint $t){
            $t->id(); $t->foreignId("application_id")->constrained("loan_applications")->onDelete("cascade");
            $t->foreignId("created_by")->constrained("users")->onDelete("cascade");
            $t->string("type",50)->default("general"); $t->text("content"); $t->boolean("is_internal")->default(true); $t->timestamps();
        });
        Schema::create("credit_reports",function(Blueprint $t){
            $t->id(); $t->foreignId("user_id")->constrained()->onDelete("cascade");
            $t->foreignId("application_id")->nullable()->constrained("loan_applications")->nullOnDelete();
            $t->string("national_id",50); $t->string("provider",50)->default("Experian");
            $t->enum("check_type",["soft","hard"])->default("soft"); $t->integer("credit_score")->nullable();
            $t->json("report_data")->nullable(); $t->json("raw_response")->nullable();
            $t->string("status",30)->default("pending"); $t->timestamp("retrieved_at")->nullable(); $t->timestamps();
        });
        Schema::create("affordability_assessments",function(Blueprint $t){
            $t->id(); $t->foreignId("application_id")->constrained("loan_applications")->onDelete("cascade");
            foreach(["monthly_earnings","tax_deduction","existing_loans_deduction","other_deductions","net_salary","transport","groceries","utilities","rent","other_expenses","total_living_expenses","disposable_income","suggested_loan_amount","max_loan_amount"] as $col)
                $t->decimal($col,12,2)->default(0);
            $t->timestamps();
        });
        Schema::create("employments",function(Blueprint $t){
            $t->id(); $t->foreignId("application_id")->constrained("loan_applications")->onDelete("cascade");
            $t->string("employer_name",200)->nullable(); $t->string("employer_type",50)->nullable();
            $t->date("employment_expiry_date")->nullable(); $t->string("department",100)->nullable();
            $t->string("job_title",100)->nullable(); $t->string("contact_number",20)->nullable(); $t->string("employment_number",50)->nullable(); $t->timestamps();
        });
        Schema::create("bank_details",function(Blueprint $t){
            $t->id(); $t->foreignId("application_id")->constrained("loan_applications")->onDelete("cascade");
            $t->string("bank_name",100)->nullable(); $t->string("account_holder_name",200)->nullable();
            $t->string("account_number",50)->nullable(); $t->string("account_type",50)->nullable(); $t->timestamps();
        });
        Schema::create("next_of_kins",function(Blueprint $t){
            $t->id(); $t->foreignId("application_id")->constrained("loan_applications")->onDelete("cascade");
            $t->string("relationship",50)->nullable(); $t->string("first_name",100)->nullable(); $t->string("last_name",100)->nullable(); $t->string("contact_number",20)->nullable(); $t->timestamps();
        });
        Schema::create("system_settings",function(Blueprint $t){
            $t->id(); $t->string("group",50)->default("general"); $t->string("key",100)->unique();
            $t->text("value")->nullable(); $t->string("type",20)->default("string"); $t->boolean("is_encrypted")->default(false); $t->timestamps();
        });
    }
    public function down():void {
        foreach(["system_settings","next_of_kins","bank_details","employments","affordability_assessments","credit_reports","application_notes","documents"] as $t) Schema::dropIfExists($t);
    }
};
