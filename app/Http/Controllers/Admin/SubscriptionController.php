<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Notifications\SubscriptionUpdate;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['user', 'membershipPlan'])->latest()->get()->map(function ($sub) {
            $sub->payment_proof_url = $sub->payment_proof ? asset('storage/' . $sub->payment_proof) : null;
            return $sub;
        });
        return Inertia::render('Admin/Subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function approve($id)
    {
        try {
            $subscription = Subscription::with('user', 'membershipPlan')->findOrFail($id);

            if (!$subscription->membershipPlan) {
                return redirect()->back()->with('error', 'Error: El plan de membresía asociado no existe.');
            }

            // Evitar aprobar de nuevo y enviar la misma notificación otra vez
            if ($subscription->payment_status === 'approved') {
                return redirect()->back()->with('message', 'Esta suscripción ya estaba aprobada.');
            }

            $subscription->update([
                'payment_status' => 'approved',
                'starts_at' => now(),
                'ends_at' => now()->addDays((int) $subscription->membershipPlan->duration_days),
            ]);

            try {
                $subscription->user->notify(new SubscriptionUpdate('approved', 'Tu suscripción ha sido aprobada. ¡Bienvenido!'));
            } catch (\Exception $e) {
                // Notificación fallida pero el proceso sigue
                \Log::error('Error enviando notificación de suscripción: ' . $e->getMessage());
            }

            return redirect()->back()->with('message', 'Suscripción aprobada con éxito.');
        } catch (\Exception $e) {
            \Log::error('Error al aprobar suscripción: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al procesar la aprobación.');
        }
    }

    public function reject($id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        $subscription->update(['payment_status' => 'rejected']);

        $subscription->user->notify(new SubscriptionUpdate('rejected', 'Tu pago no ha podido ser verificado. Por favor contacta soporte.'));

        return redirect()->back()->with('message', 'Suscripción rechazada.');
    }
}
