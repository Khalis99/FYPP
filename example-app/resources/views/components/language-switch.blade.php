{{-- EN | BM toggle. Posts to the locale route; app.js slides the thumb first for a smooth switch. --}}
@php
    $current = app()->getLocale() === 'ms' ? 'ms' : 'en';
    $next = $current === 'ms' ? 'en' : 'ms';
@endphp

<form method="POST" action="{{ route('locale.update', $next) }}" class="no-print inline-flex" data-language-switch>
    @csrf
    <button type="submit" class="lang-switch" data-lang="{{ $current }}" role="switch" aria-checked="{{ $current === 'ms' ? 'true' : 'false' }}" aria-label="{{ $current === 'ms' ? 'Bahasa Melayu — tukar kepada English' : 'English — switch to Bahasa Melayu' }}" title="{{ $current === 'ms' ? 'Tukar kepada English' : 'Tukar ke Bahasa Melayu' }}">
        <span class="lang-thumb" aria-hidden="true"></span>
        <span class="lang-option" aria-hidden="true">EN</span>
        <span class="lang-option" aria-hidden="true">BM</span>
    </button>
</form>
