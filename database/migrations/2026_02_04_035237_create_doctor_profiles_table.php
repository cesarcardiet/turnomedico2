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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('speciality_id')->nullable()->constrained()->onDelete('set null');
            $table->text('about')->nullable();
            $table->string('clinic_address')->nullable();
            $table->string('phone_number')->nullable();
            $table->json('services')->nullable();
            $table->string('working_hours')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_active')->default(false);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
