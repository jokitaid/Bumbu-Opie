<x-filament-panels::page.simple>
    <style>
        /* Sembunyikan heading bawaan Filament, biarkan judul custom tampil */
        .fi-simple-header, .fi-simple-subheader {
            display: none !important;
        }
        /* Custom ukuran logo login */
        .custom-login-logo {
            max-width: 220px;
            height: auto;
        }
    </style>
    <div class="flex flex-col items-center mb-10">
        <img src="{{ asset('images/logo-bumbu-opie.png') }}" alt="Logo" class="custom-login-logo">
        <h2 class="custom-login-subtitle text-xl font-semibold">Masuk ke akun Anda</h2>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>