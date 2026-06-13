<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicePlanRequest;
use App\Models\Category;
use App\Models\Chord;
use App\Models\Contact;
use App\Models\PlanEntry;
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
            ->withCount(['entries as songs_count' => fn ($query) => $query->where('type', PlanEntry::TYPE_SONG)])
            ->orderByDesc('date')
            ->paginate(15);

        return view('service-plans.index', compact('servicePlans'));
    }

    public function create(): View
    {
        $contacts = auth()->user()->contacts()->orderBy('name')->get();

        return view('service-plans.create', [
            'contacts' => $contacts,
            'musicalKeys' => WorshipPlan::MUSICAL_KEYS,
        ]);
    }

    public function store(StoreServicePlanRequest $request): RedirectResponse
    {
        $plan = DB::transaction(function () use ($request) {
            $plan = $request->user()->servicePlans()->create(
                $request->safe()->only(['title', 'date', 'notes', 'director_contact_id'])
            );

            $entries = $this->parseEntriesPayload($request->input('entries'));
            $this->syncPlanEntries($plan, $entries);

            return $plan;
        });

        return redirect()
            ->route('service-plans.show', $plan)
            ->with('status', 'plan-created');
    }

    public function show(ServicePlan $servicePlan): View
    {
        $this->authorizePlan($servicePlan);

        $servicePlan->load([
            'director',
            'entries' => fn ($query) => $query->with(['song.category', 'contact', 'category']),
        ]);

        $contacts = auth()->user()->contacts()->orderBy('name')->get();
        $categories = Category::query()->forUser(auth()->id())->orderBy('name')->get();

        return view('service-plans.show', [
            'servicePlan' => $servicePlan,
            'contacts' => $contacts,
            'categories' => $categories,
            'musicalKeys' => WorshipPlan::MUSICAL_KEYS,
            'builderEntries' => $this->entriesForBuilder($servicePlan),
        ]);
    }

    public function searchSongs(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim();
        $planId = $request->integer('plan');
        $excludeIds = [];

        if ($planId > 0) {
            $plan = ServicePlan::query()->find($planId);
            if ($plan && $plan->user_id === auth()->id()) {
                $excludeIds = $plan->entries()
                    ->where('type', PlanEntry::TYPE_SONG)
                    ->pluck('song_id')
                    ->all();
            }
        }

        $songs = Song::query()
            ->with('category')
            ->when($excludeIds !== [], fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $term = '%'.$search->toString().'%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('artist', 'like', $term)
                        ->orWhere('key', 'like', $term);
                });
            })
            ->orderBy('title')
            ->limit(25)
            ->get(['id', 'title', 'artist', 'key', 'category_id'])
            ->map(fn (Song $song) => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'key' => $song->key,
                'category' => $song->category?->name,
                'category_id' => $song->category_id,
            ]);

        return response()->json($songs);
    }

    public function syncSetlist(Request $request, ServicePlan $servicePlan): JsonResponse|RedirectResponse
    {
        $this->authorizePlan($servicePlan);

        $validated = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.type' => ['required', 'string', Rule::in([PlanEntry::TYPE_SECTION, PlanEntry::TYPE_SONG])],
            'entries.*.section_title' => ['nullable', 'string', 'max:255'],
            'entries.*.song_id' => ['nullable', 'integer', 'exists:songs,id'],
            'entries.*.performance_key' => ['nullable', 'string', 'max:10'],
            'entries.*.contact_id' => ['nullable', 'integer', $this->contactRule()],
            'director_contact_id' => ['nullable', 'integer', $this->contactRule()],
        ], [], [
            'entries' => 'setlist',
            'entries.*.section_title' => 'título de sección',
            'entries.*.song_id' => 'canción',
            'entries.*.performance_key' => 'tono',
            'entries.*.contact_id' => 'integrante',
            'director_contact_id' => 'director',
        ]);

        DB::transaction(function () use ($servicePlan, $validated) {
            $servicePlan->update([
                'director_contact_id' => $validated['director_contact_id'] ?? null,
            ]);
            $this->syncPlanEntries($servicePlan, $validated['entries']);
        });

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('service-plans.show', $servicePlan)
            ->with('status', 'setlist-updated');
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

  /**
     * @param  array<int, array<string, mixed>>  $entriesPayload
     */
    private function syncPlanEntries(ServicePlan $plan, array $entriesPayload): void
    {
        $plan->entries()->delete();

        $order = 1;
        $usedSongIds = [];

        foreach ($entriesPayload as $entry) {
            $type = $entry['type'] ?? PlanEntry::TYPE_SONG;

            if ($type === PlanEntry::TYPE_SECTION) {
                $title = trim((string) ($entry['section_title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $plan->entries()->create([
                    'order' => $order++,
                    'type' => PlanEntry::TYPE_SECTION,
                    'section_title' => $title,
                ]);

                continue;
            }

            if ($type !== PlanEntry::TYPE_SONG) {
                continue;
            }

            $songId = (int) ($entry['song_id'] ?? 0);
            if ($songId <= 0 || in_array($songId, $usedSongIds, true)) {
                continue;
            }

            $song = Song::query()->find($songId);
            if (! $song) {
                continue;
            }

            $usedSongIds[] = $songId;

            $plan->entries()->create([
                'order' => $order++,
                'type' => PlanEntry::TYPE_SONG,
                'song_id' => $songId,
                'category_id' => $entry['category_id'] ?? $song->category_id,
                'performance_key' => $entry['performance_key'] ?? $song->key,
                'contact_id' => $entry['contact_id'] ?? null,
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseEntriesPayload(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($raw) ? $raw : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entriesForBuilder(ServicePlan $plan): array
    {
        return $plan->entries->map(function (PlanEntry $entry) {
            if ($entry->isSection()) {
                return [
                    'type' => PlanEntry::TYPE_SECTION,
                    'section_title' => $entry->section_title,
                ];
            }

            $song = $entry->song;

            return [
                'type' => PlanEntry::TYPE_SONG,
                'song_id' => $entry->song_id,
                'title' => $song?->title ?? '',
                'artist' => $song?->artist ?? '',
                'original_key' => $song?->key ?? '',
                'performance_key' => $entry->performance_key ?? $song?->key,
                'contact_id' => $entry->contact_id,
                'category' => ($entry->category ?? $song?->category)?->name,
            ];
        })->values()->all();
    }

    private function authorizePlan(ServicePlan $servicePlan): void
    {
        abort_unless($servicePlan->user_id === auth()->id(), 403);
    }

    private function contactRule(): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('contacts', 'id')->where('user_id', auth()->id());
    }

    /**
     * @return array{servicePlan: ServicePlan, categoryMap: \Illuminate\Support\Collection, contacts: \Illuminate\Support\Collection, entries: array<int, array<string, mixed>>, diagramLibrary: array, allChordNames: array<int, string>}
     */
    private function preparePlanExportData(ServicePlan $servicePlan): array
    {
        $servicePlan->load([
            'director',
            'entries' => fn ($query) => $query->with(['song.category', 'song.chords', 'contact', 'category'])->orderBy('order'),
        ]);

        $categoryMap = Category::query()->forUser($servicePlan->user_id)->get()->keyBy('id');
        $contactMap = Contact::query()->where('user_id', $servicePlan->user_id)->get()->keyBy('id');
        $allChordNames = [];
        $entries = [];
        $songNumber = 0;

        foreach ($servicePlan->entries as $entry) {
            if ($entry->isSection()) {
                $entries[] = [
                    'type' => PlanEntry::TYPE_SECTION,
                    'order' => $entry->order,
                    'section_title' => $entry->section_title,
                ];

                continue;
            }

            $song = $entry->song;
            if (! $song) {
                continue;
            }

            $songNumber++;

            $content = $song->chords->firstWhere('instrument', 'guitar')?->content
                ?? $song->chords->firstWhere('instrument', 'keyboard')?->content
                ?? '';

            $chordNames = $content !== '' ? ChordProParser::extractChordNames($content) : [];
            $allChordNames = array_merge($allChordNames, $chordNames);

            $contact = $contactMap[$entry->contact_id] ?? null;
            $performanceKey = $entry->performance_key ?? $song->key;

            $assigned = null;
            if ($contact) {
                $parts = array_filter([$contact->role, $contact->vocal_range, $contact->vocal_tone]);
                $assigned = $contact->name.($parts ? ' ('.implode(' · ', $parts).')' : '');
            }

            $entries[] = [
                'type' => PlanEntry::TYPE_SONG,
                'id' => $song->id,
                'order' => $songNumber,
                'list_order' => $entry->order,
                'title' => $song->title,
                'artist' => $song->artist,
                'original_key' => $song->key,
                'key' => $performanceKey,
                'category' => ($categoryMap[$entry->category_id] ?? $song->category)?->name,
                'assigned' => $assigned,
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
            'contacts' => $contactMap->values(),
            'entries' => $entries,
            'diagramLibrary' => $diagramLibrary,
            'allChordNames' => $allChordNames,
        ];
    }
}
