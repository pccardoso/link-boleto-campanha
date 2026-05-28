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
    
        Schema::create('authorized_plates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plate_number')->unique();
            $table->integer('vehicle_number')->unique();
            $table->string('contract_date')->nullable();
            $table->string('vehicle_status')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('volunteer')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('cell_phone')->nullable();
            $table->decimal('fixed_value', 10, 2)->nullable();
            $table->decimal('agreement_value', 10, 2)->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorized_plates');
    }
};
