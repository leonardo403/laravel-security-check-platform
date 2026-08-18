@php
    $dark = $dark ?? false;
    $labels = [
        'pt_BR' => 'PT',
        'es' => 'ES',
        'en' => 'EN',
    ];
@endphp
<div class="flex items-center gap-1 p-0.5 rounded-lg @if($dark) bg-slate-800/60 border border-slate-700/30 @else bg-slate-800/60 border border-slate-700/30 @endif">
    @foreach(\App\Http\Middleware\SetLocale::LOCALES as $code)
        @php($active = app()->getLocale() === $code)
        <a href="{{ route('locale.switch', $code) }}"
           class="px-2.5 py-1 rounded-md text-xs font-medium transition-all duration-200 {{ $active
               ? 'bg-teal-500/20 text-teal-400 shadow-sm'
               : 'text-slate-500 hover:text-slate-300' }}">
            {{ $labels[$code] }}
        </a>
    @endforeach
</div>
