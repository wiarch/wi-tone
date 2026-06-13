<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Support\WorshipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $contacts = auth()->user()
            ->contacts()
            ->orderBy('name')
            ->get();

        return view('contacts.index', compact('contacts'));
    }

    public function create(): View
    {
        return view('contacts.create', $this->formOptions());
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('contacts', 'public');
        }

        $request->user()->contacts()->create($data);

        return redirect()
            ->route('contacts.index')
            ->with('status', 'contact-created');
    }

    public function edit(Contact $contact): View
    {
        $this->authorizeContact($contact);

        return view('contacts.edit', [
            'contact' => $contact,
            ...$this->formOptions(),
        ]);
    }

    public function update(StoreContactRequest $request, Contact $contact): RedirectResponse
    {
        $this->authorizeContact($contact);

        $data = $request->safe()->except(['photo', 'remove_photo']);

        if ($request->boolean('remove_photo') && $contact->photo_path) {
            Storage::disk('public')->delete($contact->photo_path);
            $data['photo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($contact->photo_path) {
                Storage::disk('public')->delete($contact->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('contacts', 'public');
        }

        $contact->update($data);

        return redirect()
            ->route('contacts.index')
            ->with('status', 'contact-updated');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorizeContact($contact);

        if ($contact->photo_path) {
            Storage::disk('public')->delete($contact->photo_path);
        }

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('status', 'contact-deleted');
    }

    /**
     * @return array{roles: list<string>, vocalRanges: list<string>, vocalTones: list<string>}
     */
    private function formOptions(): array
    {
        return [
            'roles' => WorshipPlan::ROLES,
            'vocalRanges' => WorshipPlan::VOCAL_RANGES,
            'vocalTones' => WorshipPlan::VOICE_TONES,
        ];
    }

    private function authorizeContact(Contact $contact): void
    {
        abort_unless($contact->user_id === auth()->id(), 403);
    }
}
