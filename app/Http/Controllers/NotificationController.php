<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        return Inertia::render('Notifications', [
            'notifications' => auth()->user()->notifications()->paginate(20),
        ]);
    }

    public function markAsRead($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->back();
    }

    public function latest()
    {
        $notification = auth()->user()->unreadNotifications()->latest()->first();
        return response()->json([
            'notification' => $notification
        ]);
    }

    public function recent()
    {
        return response()->json([
            'notifications' => auth()->user()->notifications()->latest()->take(5)->get()
        ]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count()
        ]);
    }
}
