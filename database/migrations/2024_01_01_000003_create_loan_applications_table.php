<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create("loan_applications",function(Blueprint $t){
            $t->id(); $t->string("application_number",30)->unique();
            $t->foreignId("user_id")->constrained()->onDelete("cascade");
            $t->foreignId("loan_product_id")->nullable()->constrained()->nullOnDelete();
            $t->foreignId("assigned_officer_id")->nullable()->constrained("users")->nullOnDelete();
            $t->enum("status",["draft","submitted","under_review","info_requested","on_hold","approved","declined","disbursed"])->default("draft");
            $t->integer("step")->default(1);
            $t->string("title",10)->nullable(); $t->string("first_name",100)->nullable(); $t->string("surname",100)->nullable();
            $t->string("national_id",50)->nullable(); $t->date("date_of_birth")->nullable();
            $t->enum("gender",["male","female","other"])->nullable(); $t->string("marital_status",30)->nullable();
            $t->string("cell_number",20)->nullable(); $t->string("email",100)->nullable();
            $t->decimal("requested_amount",12,2)->nullable(); $t->integer("requested_term")->nullable();
            $t->string("loan_purpose",255)->nullable(); $t->string("payout_method",50)->nullable(); $t->string("collection_method",50)->nullable();
            $t->decimal("approved_amount",12,2)->nullable(); $t->integer("approved_term")->nullable();
            $t->decimal("approved_interest_rate",6,4)->nullable(); $t->date("disbursement_date")->nullable();
            $t->integer("risk_score")->nullable(); $t->text("decline_reason")->nullable(); $t->text("admin_notes")->nullable();
            $t->timestamp("submitted_at")->nullable(); $t->timestamp("reviewed_at")->nullable(); $t->timestamp("decided_at")->nullable();
            $t->softDeletes(); $t->timestamps();
        });
    }
    public function down():void { Schema::dropIfExists("loan_applications"); }
};
