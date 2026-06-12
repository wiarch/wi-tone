<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicePlanRequest;
use App\Models\Category;
use App\Models\Chord;
use App\Models\ServicePlan;
use App\Models\Song;
use App\Support\ChordProParser;
use App\Support\WorshipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        return view('service-plans.create', [
            'voiceTones' => WorshipPlan::VOICE_TONES,
        ]);
    }

    public function store(StoreServicePlanRequest $request): RedirectResponse
    {
        $plan = DB::transaction(function () use ($request) {
            $plan = $request->user()->servicePlans()->create(
                $request->safe()->only(['title', 'date', 'notes'])
            );

            foreach ($request->input('members', []) as $member) {
                $name = trim((string) ($member['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $plan->teamMembers()->create([
                    'user_id' => $request->user()->id,
                    'name' => $name,
                    'voice_tone' => $member['voice_tone'],
                ]);
            }

            return $plan;
        });

        return redirect()
            ->route('service-plans.show', $plan)
            ->with('status', 'plan-created');
    }

    public function show(Request $request, ServicePlan $servicePlan): View
    {
        $this->authorizePlan($servicePlan);

        $servicePlan->load([
            'teamMembers' => fn ($query) => $query->orderBy('name'),
            'songs' => fn ($query) => $query->with('category')->orderByPivot('order'),
        ]);

        $search = $request->string('q')->trim();
        $categoryFilter = $request->integer('category');

        $availableSongs = Song::query()
            ->with('category')
            ->whereNotIn('id', $servicePlan->songs->pluck('id'))
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $term = '%'.$search->toString().'%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('artist', 'like', $term)
                        ->orWhere('key', 'like', $term);
                });
            })
            ->when($categoryFilter > 0, fn ($query) => $query->where('category_id', $categoryFilter))
            ->orderBy('title')
            ->get(['id', 'title', 'artist', 'key', 'category_id']);

        $categories = Category::query()->forUser(auth()->id())->orderBy('name')->get();
        $categoryMap = $categories->keyBy('id');

        return view('service-plans.show', [
            'servicePlan' => $servicePlan,
            'availableSongs' => $availableSongs,
            'search' => $search->toString(),
            'categoryFilter' => $categoryFilter,
            'categories' => $categories,
            'categoryMap' => $categoryMap,
            'voiceTones' => WorshipPlan::VOICE_TONES,
            'musicalKeys' => WorshipPlan::MUSICAL_KEYS,
        ]);
    }

    public function export(ServicePlan $servicePlan): View
    {
        $this->authorizePlan($servicePlan);

        return view('service-plans.export', [
            ...$this->preparePlanExportData($servicePlan),
            'pageMode' => 'export',
            'shareSettings' => $servicePlan->share_settings ?? [],
        ]);
    }

    public function share(ServicePlan $servicePlan): View
    {
        $this->authorizePlan($servicePlan);

        return view('service-plans.export', [
            ...$this->preparePlanExportData($servicePlan),
            'pageMode' => 'share',
            'shareSettings' => $servicePlan->share_settings ?? [],
        ]);
    }

    public function publicShow(string $token): View
    {
        $servicePlan = ServicePlan::query()
            ->where('share_token', $token)
            ->whereNotNull('published_at')
            ->firstOrFail();

        return view('service-plans.export', [
            ...$this->preparePlanExportData($servicePlan),
            'pageMode' => 'public',
            'shareSettings' => $servicePlan->share_settings ?? [],
        ]);
    }

    public function publish(Request $request, ServicePlan $servicePlan): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'share_settings' => ['required', 'string'],
        ]);

        $settings = json_decode($validated['share_settings'], true);
        if (! is_array($settings)) {
            return back()->withErrors(['share_settings' => 'Configuración inválida.']);
        }

        $allowed = [
            'fontSize', 'lyricColor', 'chordColor', 'paper',
            'showChords', 'showLyrics', 'showIndex', 'showDiagrams', 'transpose',
        ];

        $servicePlan->update([
            'share_token' => $servicePlan->share_token ?? Str::random(48),
            'published_at' => now(),
            'share_settings' => array_intersect_key($settings, array_flip($allowed)),
        ]);

        return redirect()
            ->route('service-plans.share', $servicePlan)
            ->with('status', 'plan-published')
            ->with('published_url', $servicePlan->fresh()->publicUrl());
    }

    public function unpublish(ServicePlan $servicePlan): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $servicePlan->update([
            'published_at' => null,
            'share_settings' => null,
        ]);

        return redirect()
            ->route('service-plans.share', $servicePlan)
            ->with('status', 'plan-unpublished');
    }

    public function attachSong(Request $request, ServicePlan $servicePlan): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'song_id' => ['required', 'integer', 'exists:songs,id'],
            'category_id' => ['nullable', 'integer', $this->categoryRule()],
            'performance_key' => ['nullable', 'string', 'max:10'],
            'team_member_id' => [
                'nullable',
                'integer',
                Rule::exists('team_members', 'id')->where('service_plan_id', $servicePlan->id),
            ],
        ], [], [
            'song_id' => 'canción',
            'category_id' => 'categoría',
            'performance_key' => 'tono',
            'team_member_id' => 'integrante',
        ]);

        $songId = (int) $validated['song_id'];

        if ($servicePlan->songs()->where('song_id', $songId)->exists()) {
            return back()->withErrors(['song_id' => 'Esta canción ya está en el plan.']);
        }

        $song = Song::query()->findOrFail($songId);

        $nextOrder = (int) DB::table('plan_song')
            ->where('service_plan_id', $servicePlan->id)
            ->max('order') + 1;

        $servicePlan->songs()->attach($songId, [
            'order' => $nextOrder,
            'category_id' => $validated['category_id'] ?? $song->category_id,
            'performance_key' => $validated['performance_key'] ?? $song->key,
            'team_member_id' => $validated['team_member_id'] ?? null,
        ]);

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'song-attached');
    }

    public function updateSong(Request $request, ServicePlan $servicePlan, Song $song): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        abort_unless($servicePlan->songs()->where('song_id', $song->id)->exists(), 404);

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', $this->categoryRule()],
            'performance_key' => ['nullable', 'string', 'max:10'],
            'team_member_id' => [
                'nullable',
                'integer',
                Rule::exists('team_members', 'id')->where('service_plan_id', $servicePlan->id),
            ],
        ], [], [
            'category_id' => 'categoría',
            'performance_key' => 'tono',
            'team_member_id' => 'integrante',
        ]);

        $servicePlan->songs()->updateExistingPivot($song->id, [
            'category_id' => $validated['category_id'] ?? null,
            'performance_key' => $validated['performance_key'] ?? null,
            'team_member_id' => $validated['team_member_id'] ?? null,
        ]);

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'entry-updated');
    }

    public function detachSong(ServicePlan $servicePlan, Song $song): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $servicePlan->songs()->detach($song->id);

        $this->renumberPlanSongs($servicePlan);

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'song-detached');
    }

    public function reorder(Request $request, ServicePlan $servicePlan): JsonResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct'],
        ]);

        $planSongIds = $servicePlan->songs()->pluck('songs.id')->all();

        foreach ($validated['order'] as $songId) {
            if (! in_array((int) $songId, $planSongIds, true)) {
                abort(422, 'Canción no pertenece al plan.');
            }
        }

        if (count($validated['order']) !== count($planSongIds)) {
            abort(422, 'Orden incompleto.');
        }

        DB::transaction(function () use ($servicePlan, $validated) {
            foreach ($validated['order'] as $index => $songId) {
                $servicePlan->songs()->updateExistingPivot($songId, ['order' => $index + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function storeMember(Request $request, ServicePlan $servicePlan): RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'voice_tone' => ['required', 'string', Rule::in(WorshipPlan::VOICE_TONES)],
        ], [], [
            'name' => 'nombre',
            'voice_tone' => 'tono de voz',
        ]);

        $servicePlan->teamMembers()->create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'voice_tone' => $validated['voice_tone'],
        ]);

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'member-added');
    }

    private function renumberPlanSongs(ServicePlan $servicePlan): void
    {
        $songIds = $servicePlan->songs()
            ->orderByPivot('order')
            ->pluck('songs.id');

        foreach ($songIds as $index => $songId) {
            $servicePlan->songs()->updateExistingPivot($songId, ['order' => $index + 1]);
        }
    }

    private function authorizePlan(ServicePlan $servicePlan): void
    {
        abort_unless($servicePlan->user_id === auth()->id(), 403);
    }

    private function categoryRule(): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('categories', 'id')->where(function ($query) {
            $query->where(function ($inner) {
                $inner->whereNull('user_id')
                    ->orWhere('user_id', auth()->id());
            });
        });
    }

    /**
     * @return array{servicePlan: ServicePlan, categoryMap: \Illuminate\Support\Collection, entries: array<int, array<string, mixed>>, diagramLibrary: array, allChordNames: array<int, string>}
     */
    private function preparePlanExportData(ServicePlan $servicePlan): array
    {
        $servicePlan->load([
            'teamMembers' => fn ($query) => $query->orderBy('name'),
            'songs' => fn ($query) => $query->with(['category', 'chords'])->orderByPivot('order'),
        ]);

        $categoryMap = Category::query()->forUser($servicePlan->user_id)->get()->keyBy('id');
        $allChordNames = [];
        $entries = [];

        foreach ($servicePlan->songs as $song) {
            $content = $song->chords->firstWhere('instrument', 'guitar')?->content
                ?? $song->chords->firstWhere('instrument', 'keyboard')?->content
                ?? '';

            $chordNames = $content !== '' ? ChordProParser::extractChordNames($content) : [];
            $allChordNames = array_merge($allChordNames, $chordNames);

            $member = $servicePlan->teamMembers->firstWhere('id', $song->pivot->team_member_id);
            $performanceKey = $song->pivot->performance_key ?? $song->key;

            $entries[] = [
                'id' => $song->id,
                'order' => $song->pivot->order,
                'title' => $song->title,
                'artist' => $song->artist,
                'original_key' => $song->key,
                'key' => $performanceKey,
                'category' => ($categoryMap[$song->pivot->category_id] ?? $song->category)?->name,
                'assigned' => $member ? $member->name.' ('.$member->voice_tone.')' : null,
                'content' => $content,
                'chord_names' => $chordNames,
            ];
        }

        $allChordNames = array_values(array_unique($allChordNames));

        $diagramLibrary = Chord::query()
            ->whereIn('name', $allChordNames)
            ->with(['diagrams' => fn ($q) => $q->orderBy('variant_name')])
            ->get()
            ->mapWithKeys(fn (Chord $chord) => [
                $chord->name => [
                    'guitar' => $chord->diagrams
                        ->where('instrument', 'guitar')
                        ->values()
                        ->map(fn ($d) => [
                            'variant_name' => $d->variant_name,
                            'representation' => $d->representation,
                        ])
                        ->all(),
                    'keyboard' => $chord->diagrams
                        ->where('instrument', 'keyboard')
                        ->values()
                        ->map(fn ($d) => [
                            'variant_name' => $d->variant_name,
                            'representation' => $d->representation,
                        ])
                        ->all(),
                ],
            ])
            ->all();

        return [
            'servicePlan' => $servicePlan,
            'categoryMap' => $categoryMap,
            'entries' => $entries,
            'diagramLibrary' => $diagramLibrary,
            'allChordNames' => $allChordNames,
        ];
    }
}
