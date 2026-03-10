<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create("users",function(Blueprint $t){
            $t->id(); $t->string("name"); $t->string("email")->unique(); $t->string("phone",20)->unique()->nullable();
            $t->string("password"); $t->enum("role",["admin","loan_officer","borrower"])->default("borrower");
            $t->boolean("is_active")->default(true); $t->string("profile_photo")->nullable();
            $t->timestamp("email_verified_at")->nullable(); $t->timestamp("last_login_at")->nullable();
            $t->rememberToken(); $t->softDeletes(); $t->timestamps();
        });
    }
    public function down():void { Schema::dropIfExists("users"); }
};
