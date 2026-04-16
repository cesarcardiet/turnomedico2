<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SpecialityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Specialities', [
            'specialities' => Speciality::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specialities,name',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('specialities', 'public');
        }

        Speciality::create($validated);

        return redirect()->back()->with('message', 'Especialidad creada con éxito.');
    }

    public function update(Request $request, Speciality $speciality)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specialities,name,' . $speciality->id,
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($speciality->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($speciality->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('specialities', 'public');
        }

        $speciality->update($validated);

        return redirect()->back()->with('message', 'Especialidad actualizada con éxito.');
    }

    public function destroy(Speciality $speciality)
    {
        if ($speciality->doctorProfiles()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar una especialidad que tiene doctores asociados.']);
        }

        $speciality->delete();

        return redirect()->back()->with('message', 'Especialidad eliminada con éxito.');
    }
}
