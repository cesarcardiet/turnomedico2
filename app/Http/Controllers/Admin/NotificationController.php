<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Broadcast', [
            'roles' => ['all', 'doctor', 'patient'],
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:all,doctor,patient',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $query = User::query();
        if ($validated['role'] !== 'all') {
            $query->role($validated['role']);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $user->notify(new GeneralNotification($validated['title'], $validated['message']));
        }

        return redirect()->back()->with('message', 'Notifications sent to ' . count($users) . ' users.');
    }
}
