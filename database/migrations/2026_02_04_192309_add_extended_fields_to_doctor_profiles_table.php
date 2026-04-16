<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->decimal('consultation_fee', 10, 2)->nullable()->after('phone_number');
            $table->text('services_description')->nullable()->after('working_hours');
            $table->text('health_care_info')->nullable()->after('services_description');
            $table->string('city')->nullable()->after('health_care_info');

            // Banking Info
            $table->string('bank_name')->nullable()->after('city');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_holder')->nullable()->after('account_number');
            $table->string('bank_swift_ifsc')->nullable()->after('account_holder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_fee',
                'services_description',
                'health_care_info',
                'city',
                'bank_name',
                'account_number',
                'account_holder',
                'bank_swift_ifsc'
            ]);
        });
    }
};
