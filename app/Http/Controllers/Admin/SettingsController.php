<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'general' => Setting::getGroup('general'),
                'mail' => Setting::getGroup('mail'),
                'firebase' => Setting::getGroup('firebase'),
                'payment' => Setting::getGroup('payment'),
            ],
            'logo_url' => Setting::get('site_logo') ? Storage::disk('public')->url(Setting::get('site_logo')) : null,
        ]);
    }

    public function update(Request $request)
    {
        $group = $request->input('group', 'general');
        $data = $request->except(['group', 'site_logo']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, $group);
        }

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::set('site_logo', $path, 'general');
        }

        return redirect()->back()->with('message', 'Configuración actualizada correctamente');
    }
}
