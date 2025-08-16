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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('contract_counter_party_id');
            $table->date('date_signed');
            $table->date('expiry_date');
            $table->unsignedBigInteger('business_unit_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('contract_counter_party_id')
                  ->references('id')
                  ->on('contract_counter_parties')
                  ->onDelete('restrict');
            
            $table->foreign('business_unit_id')
                  ->references('id')
                  ->on('business_units')
                  ->onDelete('restrict');

            // Add indexes for performance
            $table->index(['is_active']);
            $table->index(['contract_counter_party_id']);
            $table->index(['business_unit_id']);
            $table->index(['date_signed']);
            $table->index(['expiry_date']);
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
