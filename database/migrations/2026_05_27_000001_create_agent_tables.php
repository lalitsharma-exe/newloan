<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create agent_applications table
        if (!Schema::hasTable('agent_applications')) {
            Schema::create('agent_applications', function (Blueprint $table) {
                $table->id();
                $table->string('application_ref')->unique(); // AGT-APP-NNNN
                $table->string('first_name');
                $table->string('last_name');
                $table->string('national_id')->unique();
                $table->string('mobile_number');
                $table->string('agent_type'); // 'shop' or 'individual'
                
                // Shop details (nullable if individual)
                $table->string('shop_name')->nullable();
                $table->string('shop_location')->nullable(); // village/town
                $table->string('business_type')->nullable(); // Spaza, Tuck shop, Mobile money, Pharmacy, Other
                
                // Uploaded docs paths
                $table->string('national_id_path')->nullable();
                $table->string('selfie_holding_id_path')->nullable();
                $table->string('business_licence_path')->nullable(); // Optional

                // Payout details
                $table->string('payout_method'); // 'M-Pesa', 'EcoCash', 'Bank'
                $table->string('payout_number_or_details');
                $table->string('payout_account_name');
                $table->string('payout_bank_name')->nullable(); // bank-specific

                // Vetting details
                $table->string('status')->default('pending'); // pending, approved, rejected, documents_requested
                $table->text('admin_feedback')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create agent_profiles table
        if (!Schema::hasTable('agent_profiles')) {
            Schema::create('agent_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('agent_id')->unique(); // AGT-NNNN
                $table->string('agent_type'); // 'shop' or 'individual'
                
                // Business/Individual Profile
                $table->string('shop_name')->nullable();
                $table->string('shop_location')->nullable();
                $table->string('business_type')->nullable();
                
                // Payout Details
                $table->string('payout_method');
                $table->string('payout_number_or_details');
                $table->string('payout_account_name');
                $table->string('payout_bank_name')->nullable();

                // Commission Metrics
                $table->decimal('total_earned', 10, 2)->default(0.00);
                $table->decimal('pending_earnings', 10, 2)->default(0.00);

                // Agreement signing trail
                $table->string('contract_ref')->nullable(); // AGT-AGR-NNNN
                $table->timestamp('signed_at')->nullable();
                $table->text('signature_base64_path')->nullable();
                $table->string('signed_ip')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_profiles');
        Schema::dropIfExists('agent_applications');
    }
};
