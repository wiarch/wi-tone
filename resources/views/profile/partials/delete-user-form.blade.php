<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-white">{{ __('Delete Account') }}</h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <details class="rounded-xl border border-red-500/20 bg-red-500/5" @if ($errors->userDeletion->isNotEmpty()) open @endif>
        <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-red-400 hover:text-red-300">
            {{ __('Delete Account') }}
        </summary>
        <form method="post" action="{{ route('profile.destroy') }}" class="border-t border-red-500/20 p-6">
            @csrf
            @method('delete')

            <h3 class="text-base font-medium text-white">{{ __('Are you sure you want to delete your account?') }}</h3>
            <p class="mt-1 text-sm text-slate-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="text-slate-300" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full admin-input" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </details>
</section>
