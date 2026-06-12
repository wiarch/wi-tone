<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSongRequest;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SongController extends Controller
{
    public function create(): View
    {
        return view('songs.create');
    }

    public function store(StoreSongRequest $request): RedirectResponse
    {
        $song = DB::transaction(function () use ($request) {
            $song = $request->user()->songs()->create(
                $request->safe()->only(['title', 'artist', 'key'])
            );

            $this->syncChords($song, $request->validated());

            return $song;
        });

        return redirect()
            ->route('songs.show', $song)
            ->with('status', 'song-created');
    }

    public function show(Song $song): View
    {
        $this->authorizeSong($song);

        $song->load('chords');

        return view('songs.show', compact('song'));
    }

    public function edit(Song $song): View
    {
        $this->authorizeSong($song);

        $song->load('chords');

        return view('songs.edit', compact('song'));
    }

    public function update(StoreSongRequest $request, Song $song): RedirectResponse
    {
        $this->authorizeSong($song);

        DB::transaction(function () use ($request, $song) {
            $song->update($request->safe()->only(['title', 'artist', 'key']));
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
        $instruments = [
            'guitar' => $data['guitar_content'] ?? null,
            'keyboard' => $data['keyboard_content'] ?? null,
        ];

        foreach ($instruments as $instrument => $content) {
            if (filled($content)) {
                $song->chords()->updateOrCreate(
                    ['instrument' => $instrument],
                    ['content' => $content]
                );
            } else {
                $song->chords()->where('instrument', $instrument)->delete();
            }
        }
    }

    private function authorizeSong(Song $song): void
    {
        abort_unless($song->user_id === auth()->id(), 403);
    }
}
