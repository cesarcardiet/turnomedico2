<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $isProfileComplete = true;
        $profileMissingFields = [];
        if ($user && $user->hasRole('doctor')) {
            $user->unsetRelation('doctorProfile');
            $profile = \App\Models\DoctorProfile::where('user_id', $user->id)->latest('updated_at')->first();
            if (!$profile) {
                $isProfileComplete = false;
                $profileMissingFields = ['about', 'clinic_address', 'phone_number', 'speciality'];
            } else {
                $profile->refresh();
                $row = \Illuminate\Support\Facades\DB::table('doctor_profiles')
                    ->where('user_id', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->first();
                if ($row) {
                    $aboutOk = trim((string) ($row->about ?? '')) !== '';
                    $addressOk = trim((string) ($row->clinic_address ?? '')) !== '';
                    $phoneOk = trim((string) ($row->phone_number ?? '')) !== '';
                    $specialityOk = $row->speciality_id !== null && (string) $row->speciality_id !== '' && (int) $row->speciality_id > 0;
                    $isProfileComplete = $aboutOk && $addressOk && $phoneOk && $specialityOk;
                    if (!$isProfileComplete) {
                        if (!$aboutOk) $profileMissingFields[] = 'about';
                        if (!$addressOk) $profileMissingFields[] = 'clinic_address';
                        if (!$phoneOk) $profileMissingFields[] = 'phone_number';
                        if (!$specialityOk) $profileMissingFields[] = 'speciality';
                    }
                } else {
                    $isProfileComplete = $profile->isComplete();
                    if (!$isProfileComplete) {
                        $profileMissingFields = $profile->getMissingRequiredFields();
                    }
                }
            }
            if ($profile) {
                $user->setRelation('doctorProfile', $profile);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'roles' => $user->getRoleNames(),
                    'unread_notifications' => $user->unreadNotifications()->count(),
                    'pending_subscriptions_count' => $user->hasRole('admin') ? \App\Models\Subscription::where('payment_status', 'pending')->count() : 0,
                    'is_subscribed' => $user->hasRole('doctor') ? $user->subscriptions()->where('payment_status', 'approved')->where('ends_at', '>', now())->exists() : true,
                    'has_pending_subscription' => $user->hasRole('doctor') ? $user->subscriptions()->where('payment_status', 'pending')->exists() : false,
                    'doctor_profile' => $user->hasRole('doctor') ? $user->doctorProfile : null,
                    'is_profile_complete' => $user->hasRole('doctor') ? $isProfileComplete : true,
                    'profile_missing_fields' => $user->hasRole('doctor') ? $profileMissingFields : [],
                ] : null,
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
                'appointment' => $request->session()->get('appointment'),
                'profile_missing_after_save' => $request->session()->get('profile_missing_after_save', []),
            ],
            'site_logo' => \App\Models\Setting::get('site_logo') ? \Illuminate\Support\Facades\Storage::disk('public')->url(\App\Models\Setting::get('site_logo')) : null,
            'firebase_config' => [
                'apiKey' => \App\Models\Setting::get('firebase_api_key'),
                'authDomain' => \App\Models\Setting::get('firebase_auth_domain'),
                'projectId' => \App\Models\Setting::get('firebase_project_id'),
                'storageBucket' => \App\Models\Setting::get('firebase_storage_bucket'),
                'messagingSenderId' => \App\Models\Setting::get('firebase_messaging_sender_id'),
                'appId' => \App\Models\Setting::get('firebase_app_id'),
                'measurementId' => \App\Models\Setting::get('firebase_measurement_id'),
            ],
        ];
    }
}
