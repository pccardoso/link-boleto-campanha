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
    
        Schema::create('history_plate', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('authorized_plate_id')->nullable();
            $table->foreign('authorized_plate_id')->references('id')->on('authorized_plates')->onDelete('set null');
            $table->integer('status');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_plate');
    }
};
