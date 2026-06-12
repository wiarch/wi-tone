<?php

namespace App\Http\Controllers;

use App\Models\Chord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChordController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim();

        $chords = Chord::query()
            ->when($query->isNotEmpty(), function ($builder) use ($query) {
                $term = '%'.$query->toString().'%';
                $builder->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('root_note', 'like', $term);
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'root_note']);

        return response()->json($chords);
    }

    public function diagrams(Request $request): JsonResponse
    {
        $name = $request->string('name')->trim();

        if ($name->isEmpty()) {
            return response()->json(['message' => 'Nombre de acorde requerido.'], 422);
        }

        $chord = Chord::query()
            ->where('name', $name->toString())
            ->with(['diagrams' => fn ($q) => $q->orderBy('variant_name')])
            ->first();

        if (! $chord) {
            return response()->json(['message' => 'Acorde no encontrado.'], 404);
        }

        return response()->json([
            'name' => $chord->name,
            'root_note' => $chord->root_note,
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
        ]);
    }
}
