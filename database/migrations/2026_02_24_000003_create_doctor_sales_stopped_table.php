<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_sales_stopped', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
            $table->unique(['doctor_profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_sales_stopped');
    }
};
