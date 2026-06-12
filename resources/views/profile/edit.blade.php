<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">{{ __('Profile') }}</h1>
        <p class="mt-1 text-sm text-slate-500">Configuración de tu cuenta</p>
    </div>

    <div class="max-w-3xl space-y-6">
        <x-admin-card>
            <div class="p-6 sm:p-8 admin-form">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-admin-card>

        <x-admin-card>
            <div class="p-6 sm:p-8 admin-form">
                @include('profile.partials.update-password-form')
            </div>
        </x-admin-card>

        <x-admin-card>
            <div class="p-6 sm:p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </x-admin-card>
    </div>
</x-app-layout>
