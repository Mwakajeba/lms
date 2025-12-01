@php
    $languages = [
        'en' => ['name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
        'sw' => ['name' => 'Swahili', 'native_name' => 'Kiswahili', 'flag' => '🇹🇿'],
        'fr' => ['name' => 'French', 'native_name' => 'Français', 'flag' => '🇫🇷'],
        'es' => ['name' => 'Spanish', 'native_name' => 'Español', 'flag' => '🇪🇸'],
        'ar' => ['name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦']
    ];
    $currentLocale = app()->getLocale();
@endphp

<div class="language-switcher dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="me-1">{{ $languages[$currentLocale]['flag'] }}</span>
        <span class="d-none d-md-inline">{{ $languages[$currentLocale]['native_name'] }}</span>
    </button>
    <ul class="dropdown-menu">
        @foreach($languages as $locale => $language)
            <li>
                <a class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}" 
                   href="{{ route('language.switch', $locale) }}">
                    <span class="me-2">{{ $language['flag'] }}</span>
                    <span>{{ $language['native_name'] }}</span>
                    @if($locale === $currentLocale)
                        <i class="bx bx-check ms-auto"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div> 