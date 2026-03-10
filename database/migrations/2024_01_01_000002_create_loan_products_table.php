<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create("loan_products",function(Blueprint $t){
            $t->id(); $t->string("name",100)->unique(); $t->string("slug",100)->unique();
            $t->decimal("interest_rate",6,4); $t->decimal("min_amount",12,2)->default(500); $t->decimal("max_amount",12,2);
            $t->integer("min_term_months")->default(1); $t->integer("max_term_months")->default(60);
            $t->decimal("processing_fee",10,2)->default(0); $t->enum("processing_fee_type",["fixed","percentage"])->default("percentage");
            $t->decimal("late_payment_fee",10,2)->default(0); $t->text("description")->nullable();
            $t->text("eligibility_criteria")->nullable(); $t->boolean("is_active")->default(true); $t->timestamps();
        });
    }
    public function down():void { Schema::dropIfExists("loan_products"); }
};
