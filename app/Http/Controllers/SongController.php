<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSongRequest;
use App\Models\Category;
use App\Models\Chord;
use App\Models\Song;
use App\Support\ChordProParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SongController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim();

        $categoryId = $request->integer('category');

        $songs = auth()->user()
            ->songs()
            ->with('category')
            ->when($query->isNotEmpty(), function ($builder) use ($query) {
                $term = '%'.$query->toString().'%';
                $builder->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('artist', 'like', $term)
                        ->orWhere('key', 'like', $term);
                });
            })
            ->when($categoryId > 0, fn ($builder) => $builder->where('category_id', $categoryId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()->forUser(auth()->id())->orderBy('name')->get();

        return view('songs.index', compact('songs', 'categories', 'categoryId'));
    }

    public function create(): View
    {
        return view('songs.create', [
            'categories' => $this->categoriesForUser(),
        ]);
    }

    public function store(StoreSongRequest $request): RedirectResponse
    {
        $song = DB::transaction(function () use ($request) {
            $song = $request->user()->songs()->create(
                $request->safe()->only(['title', 'artist', 'key', 'category_id'])
            );

            $this->syncChords($song, $request->validated());

            return $song;
        });

        return redirect()
            ->route('songs.show', $song)
            ->with('status', 'song-created');
    }

    public function show(Request $request, Song $song): View
    {
        $this->authorizeSong($song);

        $data = $this->prepareSongDisplayData($song);
        $viewMode = $request->query('view', 'guitar');
        if (! in_array($viewMode, ['guitar', 'keyboard'], true)) {
            $viewMode = 'guitar';
        }

        return view('songs.show', [
            ...$data,
            'viewMode' => $viewMode,
            'stageMode' => $request->boolean('stage'),
        ]);
    }

    public function export(Song $song): View
    {
        $this->authorizeSong($song);

        return view('songs.export', $this->prepareSongDisplayData($song));
    }

    /**
     * @return array{song: Song, content: string, parsedLines: array, diagramLibrary: array, songChordNames: array}
     */
    private function prepareSongDisplayData(Song $song): array
    {
        $song->load('chords');

        $content = $song->chords->firstWhere('instrument', 'guitar')?->content
            ?? $song->chords->firstWhere('instrument', 'keyboard')?->content
            ?? '';

        $parsedLines = $content !== '' ? ChordProParser::parse($content) : [];
        $chordNames = $content !== '' ? ChordProParser::extractChordNames($content) : [];

        $diagramLibrary = Chord::query()
            ->whereIn('name', $chordNames)
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
            'song' => $song,
            'content' => $content,
            'parsedLines' => $parsedLines,
            'diagramLibrary' => $diagramLibrary,
            'songChordNames' => $chordNames,
        ];
    }

    public function edit(Song $song): View
    {
        $this->authorizeSong($song);

        $song->load('chords');

        return view('songs.edit', [
            'song' => $song,
            'categories' => $this->categoriesForUser(),
        ]);
    }

    public function update(StoreSongRequest $request, Song $song): RedirectResponse
    {
        $this->authorizeSong($song);

        DB::transaction(function () use ($request, $song) {
            $song->update($request->safe()->only(['title', 'artist', 'key', 'category_id']));
            $this->syncChords($song, $request->validated());
        });

        return redirect()
            ->route('songs.show', $song)
            ->with('status', 'song-updated');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncChords(Song $song, array $data): void
    {
        $content = $data['content'] ?? null;

        if (filled($content)) {
            $song->chords()->updateOrCreate(
                ['instrument' => 'guitar'],
                ['content' => $content]
            );
        } else {
            $song->chords()->delete();
        }
    }

    private function authorizeSong(Song $song): void
    {
        abort_unless($song->user_id === auth()->id(), 403);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function categoriesForUser()
    {
        return Category::query()
            ->forUser(auth()->id())
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();
    }
}
