<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class MembershipController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $subscription = $user->subscriptions()->latest()->first();

        // If user has a pending or active subscription, show status page
        if ($subscription && ($subscription->payment_status === 'pending' || ($subscription->payment_status === 'approved' && $subscription->ends_at->isFuture()))) {
            return Inertia::render('Doctor/SubscriptionStatus', [
                'subscription' => $subscription->load('membershipPlan'),
                'history' => $user->subscriptions()->with('membershipPlan')->latest()->get(),
                'payment_details' => \App\Models\Setting::getGroup('payment'),
            ]);
        }

        return Inertia::render('Doctor/Membership', [
            'plans' => MembershipPlan::where('is_active', true)->get(),
            'payment_details' => \App\Models\Setting::getGroup('payment'),
        ]);
    }

    public function subscribe(Request $request)
    {
        $plan = MembershipPlan::findOrFail($request->input('membership_plan_id'));
        $isFreePlan = (float) $plan->price === 0.0;

        if ($isFreePlan) {
            $request->validate([
                'membership_plan_id' => 'required|exists:membership_plans,id',
            ]);
            $proofPath = null;
            $referenceNumber = null;
            $notes = null;
        } else {
            $request->validate([
                'membership_plan_id' => 'required|exists:membership_plans,id',
                'reference_number' => 'required|string',
                'payment_proof' => 'required|image|max:2048',
                'notes' => 'nullable|string'
            ]);
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            $referenceNumber = $request->reference_number;
            $notes = $request->notes;
        }

        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            'membership_plan_id' => $request->membership_plan_id,
            'payment_status' => 'pending',
            'payment_method' => $isFreePlan ? null : 'manual',
            'payment_proof' => $proofPath,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);

        $admins = \App\Models\User::role('admin')->get();
        if ($isFreePlan) {
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SubscriptionUpdate('pending', 'Nueva solicitud de plan gratuito: El Dr. ' . auth()->user()->name . ' ha solicitado activar un plan sin costo.'));
            return redirect()->route('doctor.membership.index')->with('message', 'Solicitud enviada. Un administrador aprobará tu plan en breve.');
        }

        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SubscriptionUpdate('pending', 'NUEVO PAGO: El Dr. ' . auth()->user()->name . ' ha enviado un comprobante de membresía.'));
        return redirect()->route('doctor.membership.index')->with('message', 'Comprobante enviado. Su suscripción está siendo verificada.');
    }

    public function editProfile()
    {
        return Inertia::render('Doctor/EditProfile', [
            'profile' => auth()->user()->doctorProfile,
            'specialities' => Speciality::all(),
            'cities' => \App\Models\City::where('is_active', true)->orderBy('name')->get(),
            'auth' => [
                'user' => auth()->user()
            ],
            'google_maps_api_key' => \App\Models\Setting::get('google_maps_api_key'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'profile_photo' => 'nullable|image|max:4096',
            'speciality_id' => 'required|exists:specialities,id',
            'about' => 'required|string|min:1',
            'clinic_address' => 'required|string|min:1',
            'phone_number' => 'required|string|min:1',
            'working_hours' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'services_description' => 'nullable|string',
            'health_care_info' => 'nullable|string',
            'city' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'bank_swift_ifsc' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\Log::info('Doctor profile update attempt', ['user_id' => $user->id, 'has_photo' => $request->hasFile('profile_photo')]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->save();
        }

        $data = $request->except(['profile_photo', '_method', 'address_search']);
        if (array_key_exists('latitude', $data) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (array_key_exists('longitude', $data) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }
        $data = array_intersect_key($data, array_flip((new DoctorProfile())->getFillable()));
        // Asegurar que los 4 campos obligatorios se guarden con lo enviado en el request
        $data['about'] = trim((string) $request->input('about', ''));
        $data['clinic_address'] = trim((string) $request->input('clinic_address', ''));
        $data['phone_number'] = trim((string) $request->input('phone_number', ''));
        $data['speciality_id'] = $request->input('speciality_id') ? (int) $request->input('speciality_id') : null;
        \Illuminate\Support\Facades\Log::info('Doctor profile saving', [
            'user_id' => $user->id,
            'about_len' => strlen($data['about']),
            'clinic_address_len' => strlen($data['clinic_address']),
            'phone_number_len' => strlen($data['phone_number']),
            'speciality_id' => $data['speciality_id'],
        ]);
        DoctorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        $profile = DoctorProfile::where('user_id', $user->id)->first();
        $missing = [];
        if (!$profile || trim((string) ($profile->about ?? '')) === '') {
            $missing[] = 'Sobre nosotros';
        }
        if (!$profile || trim((string) ($profile->clinic_address ?? '')) === '') {
            $missing[] = 'Dirección de la clínica';
        }
        if (!$profile || trim((string) ($profile->phone_number ?? '')) === '') {
            $missing[] = 'Teléfono';
        }
        if (!$profile || empty($profile->speciality_id)) {
            $missing[] = 'Especialidad';
        }

        return redirect()->back()
            ->with('message', 'Perfil actualizado exitosamente.')
            ->with('profile_missing_after_save', $missing);
    }
}
