<x-app-layout>
    <x-slot name="header">
        <div class="kefrec-card">
            <div class="kefrec-card-header">
                <div class="kefrec-card-title">Pengaturan</div>
                <div class="kefrec-card-subtitle">Kelola profil dan keamanan akun Anda</div>
            </div>
        </div>
    </x-slot>

    <div class="stack" style="gap: 1.2rem;">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <div class="kefrec-card">
                <div class="kefrec-card-body">
                    @livewire('profile.update-profile-information-form')
                </div>
            </div>

            <div style="height: 1px; background: #333;"></div>
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div class="kefrec-card">
                <div class="kefrec-card-body">
                    @livewire('profile.update-password-form')
                </div>
            </div>

            <div style="height: 1px; background: #333;"></div>
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="kefrec-card">
                <div class="kefrec-card-body">
                    @livewire('profile.two-factor-authentication-form')
                </div>
            </div>

            <div style="height: 1px; background: #333;"></div>
        @endif

        <div class="kefrec-card">
            <div class="kefrec-card-body">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <div style="height: 1px; background: #333;"></div>

            <div class="kefrec-card">
                <div class="kefrec-card-body">
                    @livewire('profile.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
