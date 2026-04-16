<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipPlan;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Plan Básico',
                'description' => 'Ahorra un 50% de descuento',
                'price' => 10.00,
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Profesional',
                'description' => 'Ahorra un 50% de descuento',
                'price' => 20.00,
                'duration_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Premium',
                'description' => 'Ahorra un 50% de descuento',
                'price' => 30.00,
                'duration_days' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Elite',
                'description' => 'Ahorra un 50% de descuento',
                'price' => 40.00,
                'duration_days' => 270,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
