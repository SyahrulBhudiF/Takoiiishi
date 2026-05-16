@php
    $user = filament()->auth()->user();
@endphp

@if ($user)
    <div class="takoyaki-sidebar-user">
        <div class="takoyaki-sidebar-user__identity">
            <x-filament-panels::avatar.user :user="$user" loading="lazy" />

            <div class="takoyaki-sidebar-user__meta">
                <div class="takoyaki-sidebar-user__name">{{ filament()->getUserName($user) }}</div>
                <div class="takoyaki-sidebar-user__role">{{ $user->role?->label() }}</div>
            </div>
        </div>

        @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
            <div class="takoyaki-sidebar-user__theme">
                <x-filament-panels::theme-switcher />
            </div>
        @endif

        <form action="{{ filament()->getLogoutUrl() }}" method="post">
            @csrf

            <button type="submit" class="takoyaki-sidebar-user__logout">
                <x-filament::icon
                    :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                    class="takoyaki-sidebar-user__logout-icon"
                />
                <span>Keluar</span>
            </button>
        </form>
    </div>
@endif
