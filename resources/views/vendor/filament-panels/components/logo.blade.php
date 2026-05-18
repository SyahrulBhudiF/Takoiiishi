@php
    $brandName = filament()->getBrandName();
    $brandLogo = filament()->getBrandLogo();
    $brandLogoHeight = filament()->getBrandLogoHeight() ?? '1.5rem';
    $darkModeBrandLogo = filament()->getDarkModeBrandLogo();
    $hasDarkModeBrandLogo = filled($darkModeBrandLogo);

    $getLogoClasses = fn (bool $isDarkMode): string => \Illuminate\Support\Arr::toCssClasses([
        'fi-logo',
        'fi-logo-light' => $hasDarkModeBrandLogo && (! $isDarkMode),
        'fi-logo-dark' => $isDarkMode,
        'takoyaki-logo',
    ]);

    $getLogoWrapperClasses = fn (bool $isDarkMode): string => \Illuminate\Support\Arr::toCssClasses([
        'fi-logo',
        'fi-logo-light' => $hasDarkModeBrandLogo && (! $isDarkMode),
        'fi-logo-dark' => $isDarkMode,
    ]);

    $logoStyles = "height: {$brandLogoHeight}";
    $logoWrapperStyles = "display: inline-flex; align-items: center; gap: 0.5rem";
@endphp

@capture($content, $logo, $isDarkMode = false)
    @if ($logo instanceof \Illuminate\Contracts\Support\Htmlable)
        <div
            {{
                $attributes
                    ->class([$getLogoClasses($isDarkMode)])
                    ->style([$logoStyles])
            }}
        >
            {{ $logo }}
        </div>
    @elseif (filled($logo))
        <div
            {{
                $attributes
                    ->class([$getLogoWrapperClasses($isDarkMode)])
                    ->style([$logoWrapperStyles])
            }}
        >
            <img
                class="takoyaki-logo shrink-0"
                alt="{{ __('filament-panels::layout.logo.alt', ['name' => $brandName]) }}"
                src="{{ $logo }}"
                style="{{ $logoStyles }}; display: block;"
            />
            <span style="display: block; transform: translateY(1px);" class="text-lg font-semibold leading-none tracking-tight text-gray-950 dark:text-white">
                {{ $brandName }}
            </span>
        </div>
    @else
        <div
            {{
                $attributes->class([
                    $getLogoClasses($isDarkMode),
                ])
            }}
        >
            {{ $brandName }}
        </div>
    @endif
@endcapture

{{ $content($brandLogo) }}

@if ($hasDarkModeBrandLogo)
    {{ $content($darkModeBrandLogo, isDarkMode: true) }}
@endif
