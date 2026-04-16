<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Cities', [
            'cities' => City::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'is_active' => 'sometimes|boolean'
        ]);

        City::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('message', 'Ciudad añadida correctamente');
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'is_active' => 'required|boolean'
        ]);

        $city->update($validated);

        return redirect()->back()->with('message', 'Ciudad actualizada correctamente');
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->back()->with('message', 'Ciudad eliminada correctamente');
    }
}
