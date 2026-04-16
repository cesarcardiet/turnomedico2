<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboard;
use App\Http\Controllers\Patient\DashboardController as PatientDashboard;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'specialities' => \App\Models\Speciality::all(),
        'featured_doctors' => \App\Models\DoctorProfile::with(['user', 'speciality'])
            ->where('is_approved', true)
            ->where('is_active', true)
            ->whereNotNull('about')
            ->latest()
            ->limit(15)
            ->get()
            ->filter(fn($d) => $d->isComplete())
            ->take(8)
            ->values()
            ->map(function ($doctor) {
                $doctor->is_favorited = auth()->check() ? auth()->user()->favorites()->where('doctor_profile_id', $doctor->id)->exists() : false;
                return $doctor;
            }),
    ]);
})->name('welcome');

Route::get('/queue/{doctor}', [App\Http\Controllers\QueueController::class, 'show'])->name('queue.monitor');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('admin'))
            return redirect()->route('admin.dashboard');
        if ($user->hasRole('doctor'))
            return redirect()->route('doctor.dashboard');
        return redirect()->route('patient.dashboard');
    })->name('dashboard');

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Doctors
        Route::get('/doctors', [App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/{id}/profile', [App\Http\Controllers\Admin\DoctorController::class, 'showProfile'])->name('doctors.show');
        Route::post('/doctors/{id}/approve', [App\Http\Controllers\Admin\DoctorController::class, 'approve'])->name('doctors.approve');
        Route::put('/doctors/{id}', [App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{id}', [App\Http\Controllers\Admin\DoctorController::class, 'destroy'])->name('doctors.destroy');

        // Subscriptions
        Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/{id}/approve', [App\Http\Controllers\Admin\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{id}/reject', [App\Http\Controllers\Admin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');

        // Broadcast
        Route::get('/broadcast', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('broadcast.index');
        Route::post('/broadcast/send', [App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('broadcast.send');

        // Specialities
        Route::post('specialities/{speciality}', [App\Http\Controllers\Admin\SpecialityController::class, 'update'])->name('specialities.update');
        Route::resource('specialities', App\Http\Controllers\Admin\SpecialityController::class)->except(['create', 'edit', 'show', 'update']);

        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

        // Plans
        Route::resource('plans', App\Http\Controllers\Admin\MembershipPlanController::class)->except(['create', 'edit', 'show']);

        // Cities
        Route::resource('cities', App\Http\Controllers\Admin\CityController::class)->except(['create', 'edit', 'show']);
    });

    // General Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [App\Http\Controllers\NotificationController::class, 'latest'])->name('notifications.latest');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // Doctor Routes
    Route::middleware(['role:doctor', 'subscription'])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/dashboard', [DoctorDashboard::class, 'index'])->name('dashboard');

        // Membership
        Route::get('/membership', [App\Http\Controllers\Doctor\MembershipController::class, 'index'])->name('membership.index')->withoutMiddleware(['subscription']);
        Route::post('/membership/subscribe', [App\Http\Controllers\Doctor\MembershipController::class, 'subscribe'])->name('membership.subscribe')->withoutMiddleware(['subscription']);

        // Profile
        Route::get('/profile', [App\Http\Controllers\Doctor\MembershipController::class, 'editProfile'])->name('profile.edit');
        Route::post('/profile', [App\Http\Controllers\Doctor\MembershipController::class, 'updateProfile'])->name('profile.update');
        Route::get('/profile-check', function () {
            $user = auth()->user();
            if (!$user || !$user->hasRole('doctor')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            $row = \Illuminate\Support\Facades\DB::table('doctor_profiles')->where('user_id', $user->id)->first();
            if (!$row) {
                return response()->json([
                    'user_id' => $user->id,
                    'profile_exists' => false,
                    'message' => 'No hay fila en doctor_profiles para este usuario.',
                ]);
            }
            $aboutOk = trim((string) ($row->about ?? '')) !== '';
            $addressOk = trim((string) ($row->clinic_address ?? '')) !== '';
            $phoneOk = trim((string) ($row->phone_number ?? '')) !== '';
            $specialityOk = $row->speciality_id !== null && (string) $row->speciality_id !== '' && (int) $row->speciality_id > 0;
            return response()->json([
                'user_id' => $user->id,
                'profile_exists' => true,
                'is_complete' => $aboutOk && $addressOk && $phoneOk && $specialityOk,
                'checks' => [
                    'about' => $aboutOk,
                    'clinic_address' => $addressOk,
                    'phone_number' => $phoneOk,
                    'speciality_id' => $specialityOk,
                ],
                'raw_lengths' => [
                    'about' => strlen(trim((string) ($row->about ?? ''))),
                    'clinic_address' => strlen(trim((string) ($row->clinic_address ?? ''))),
                    'phone_number' => strlen(trim((string) ($row->phone_number ?? ''))),
                ],
                'speciality_id_value' => $row->speciality_id,
            ]);
        })->name('profile.check')->withoutMiddleware(['subscription']);
        Route::get('/bank-details', [App\Http\Controllers\Doctor\MembershipController::class, 'editProfile'])->name('bank-details.index');

        // Appointments
        Route::get('/appointments', [App\Http\Controllers\Doctor\AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/appointments/{id}/status', [App\Http\Controllers\Doctor\AppointmentController::class, 'updateStatus'])->name('appointments.update-status');

        Route::get('/schedule', [App\Http\Controllers\Doctor\ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule', [App\Http\Controllers\Doctor\ScheduleController::class, 'store'])->name('schedule.store');
        Route::post('/schedule/by-day', [App\Http\Controllers\Doctor\ScheduleController::class, 'storeByDay'])->name('schedule.by-day');
        Route::post('/schedule/by-day-range', [App\Http\Controllers\Doctor\ScheduleController::class, 'storeByDayRange'])->name('schedule.by-day-range');
        Route::post('/schedule/weekly', [App\Http\Controllers\Doctor\ScheduleController::class, 'storeWeekly'])->name('schedule.weekly.store');
        Route::post('/schedule/stop-sales', [App\Http\Controllers\Doctor\ScheduleController::class, 'stopSales'])->name('schedule.stop-sales');
        Route::post('/schedule/resume-sales', [App\Http\Controllers\Doctor\ScheduleController::class, 'resumeSales'])->name('schedule.resume-sales');
        Route::delete('/schedule/weekly/{id}', [App\Http\Controllers\Doctor\ScheduleController::class, 'destroyWeekly'])->name('schedule.weekly.destroy');
        Route::post('/schedule/generate', [App\Http\Controllers\Doctor\ScheduleController::class, 'generateTimeSlots'])->name('schedule.generate');
        Route::delete('/schedule/{id}', [App\Http\Controllers\Doctor\ScheduleController::class, 'destroy'])->name('schedule.destroy');

        // Approve Payments
        Route::get('/payments', [App\Http\Controllers\Doctor\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{id}/approve', [App\Http\Controllers\Doctor\PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/{id}/reject', [App\Http\Controllers\Doctor\PaymentController::class, 'reject'])->name('payments.reject');

        Route::get('/payments/history', function () {
            return Inertia::render('Doctor/Placeholder', ['title' => 'Historial de Pagos']);
        })->name('payments.history');


        // Holidays
        Route::get('/holidays', [App\Http\Controllers\Doctor\HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [App\Http\Controllers\Doctor\HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{id}', [App\Http\Controllers\Doctor\HolidayController::class, 'destroy'])->name('holidays.destroy');

        // Other Placeholders for Sidebar
        Route::get('/reviews', function () {
            return Inertia::render('Doctor/Placeholder', ['title' => 'Reseñas']);
        })->name('reviews.index');
        Route::get('/earnings', [App\Http\Controllers\Doctor\EarningsController::class, 'index'])->name('earnings.index');

        Route::get('/password', function () {
            return Inertia::render('Doctor/Placeholder', ['title' => 'Cambiar Contraseña']);
        })->name('password.index');
    });

    // Patient Routes (Protected)
    // Patient Routes (Protected)
    Route::middleware(['role:patient'])->prefix('patient')->name('patient.')->group(function () {
        Route::get('/dashboard', [PatientDashboard::class, 'index'])->name('dashboard');
        Route::post('/appointments', [App\Http\Controllers\Patient\AppointmentController::class, 'store'])->name('appointments.store');

        // Favorites
        Route::get('/favorites', [App\Http\Controllers\Patient\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites', [App\Http\Controllers\Patient\FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{id}', [App\Http\Controllers\Patient\FavoriteController::class, 'destroy'])->name('favorites.destroy');

        // Reviews
        Route::get('/reviews', [App\Http\Controllers\Patient\ReviewController::class, 'index'])->name('reviews.index');

        // Appointments History
        Route::get('/appointments-history', [App\Http\Controllers\Patient\AppointmentController::class, 'index'])->name('appointments.index');
    });
});

// Public Search Routes
Route::prefix('patient')->name('patient.')->group(function () {
    // Pantalla de Turnos Pública (Monitor TV)
    Route::get('/queue/{id}', [App\Http\Controllers\QueueController::class, 'show'])->name('public.queue');
    Route::get('/api/queue/{id}', [App\Http\Controllers\QueueController::class, 'getQueueData'])->name('api.public.queue');
    Route::get('/search', [App\Http\Controllers\Patient\SearchController::class, 'index'])->name('search');
    Route::get('/autocomplete', [App\Http\Controllers\Patient\SearchController::class, 'autocomplete'])->name('autocomplete');
    Route::get('/doctors/{id}', [App\Http\Controllers\Patient\SearchController::class, 'show'])->name('doctor.profile');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/fcm-token', [App\Http\Controllers\DeviceTokenController::class, 'update'])->name('fcm.token');
});

require __DIR__ . '/auth.php';
