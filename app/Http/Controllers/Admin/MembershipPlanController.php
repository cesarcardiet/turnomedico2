<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipPlanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Plans', [
            'plans' => MembershipPlan::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:membership_plans,name',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        MembershipPlan::create($validated);

        return redirect()->back()->with('message', 'Plan de membresía creado con éxito.');
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:membership_plans,name,' . $plan->id,
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $plan->update($validated);

        return redirect()->back()->with('message', 'Plan de membresía actualizado con éxito.');
    }

    public function destroy(MembershipPlan $plan)
    {
        if ($plan->subscriptions()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar un plan que tiene suscripciones activas.']);
        }

        $plan->delete();

        return redirect()->back()->with('message', 'Plan de membresía eliminado con éxito.');
    }
}
