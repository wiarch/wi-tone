<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicePlanRequest;
use App\Models\ServicePlan;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServicePlanController extends Controller
{
    public function index(): View
    {
        $servicePlans = auth()->user()
            ->servicePlans()
            ->withCount('songs')
            ->orderByDesc('date')
            ->paginate(15);

        return view('service-plans.index', compact('servicePlans'));
    }

    public function create(): View
    {
        return view('service-plans.create');
    }

    public function store(StoreServicePlanRequest $request): RedirectResponse
    {
        $plan = $request->user()->servicePlans()->create(
            $request->validated()
        );

        return redirect()
            ->route('service-plans.show', $plan)
            ->with('status', 'plan-created');
    }

    public function show(ServicePlan $servicePlan): View
    {
        $this->authorizePlan($servicePlan);

        $servicePlan->load(['songs' => fn ($query) => $query->orderByPivot('order')]);

        $availableSongs = Song::query()
            ->whereNotIn('id', $servicePlan->songs->pluck('id'))
            ->orderBy('title')
            ->get(['id', 'title', 'artist', 'key']);

        return view('service-plans.show', [
            'servicePlan' => $servicePlan,
            'availableSongs' => $availableSongs,
        ]);
    }

    public function attachSong(Request $request, ServicePlan $servicePlan): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'song_id' => ['required', 'integer', 'exists:songs,id'],
        ], [], [
            'song_id' => 'canción',
        ]);

        $songId = (int) $validated['song_id'];

        if ($servicePlan->songs()->where('song_id', $songId)->exists()) {
            return back()->withErrors(['song_id' => 'Esta canción ya está en el plan.']);
        }

        $nextOrder = (int) DB::table('plan_song')
            ->where('service_plan_id', $servicePlan->id)
            ->max('order') + 1;

        $servicePlan->songs()->attach($songId, ['order' => $nextOrder]);

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'song-attached');
    }

    private function authorizePlan(ServicePlan $servicePlan): void
    {
        abort_unless($servicePlan->user_id === auth()->id(), 403);
    }
}
