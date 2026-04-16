<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => 'required|string|in:patient,doctor',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // Assign Role
        $user->assignRole($request->role);

        // If Doctor, create initial profile
        if ($request->role === 'doctor') {
            \App\Models\DoctorProfile::create([
                'user_id' => $user->id,
                'is_approved' => false, // Needs admin approval
                'is_active' => true,
            ]);
        }

        event(new \Illuminate\Auth\Events\Registered($user));

        // Notify Admins if it's a doctor
        if ($request->role === 'doctor') {
            $admins = User::role('admin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DoctorRegistered($user));
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
