<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $songsCount = $user->songs()->count();
        $plansCount = $user->servicePlans()->count();
        $repertoireCount = Song::count();
        $upcomingPlansCount = $user->servicePlans()->whereDate('date', '>=', today())->count();
        $setlistSongsCount = (int) DB::table('plan_song')
            ->whereIn('service_plan_id', $user->servicePlans()->pluck('id'))
            ->count();

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
            'repertoireCount',
            'upcomingPlansCount',
            'setlistSongsCount',
            'upcomingPlan',
            'recentSongs',
            'recentPlans',
        ));
    }
}
