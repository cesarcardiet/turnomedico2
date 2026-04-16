<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = auth()->user()->reviews()
            ->with(['doctorProfile.user'])
            ->latest()
            ->get();

        return Inertia::render('Patient/Reviews', [
            'reviews' => $reviews
        ]);
    }
}
