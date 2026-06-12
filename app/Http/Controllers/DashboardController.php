<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $songsCount = $user->songs()->count();
        $plansCount = $user->servicePlans()->count();

        $upcomingPlan = $user->servicePlans()
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->first();

        $recentSongs = $user->songs()
            ->latest()
            ->limit(5)
            ->get();

        $recentPlans = $user->servicePlans()
            ->withCount('songs')
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'songsCount',
            'plansCount',
            'upcomingPlan',
            'recentSongs',
            'recentPlans',
        ));
    }
}
