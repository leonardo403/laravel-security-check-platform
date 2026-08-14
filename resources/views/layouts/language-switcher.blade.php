@php
    $dark = $dark ?? false;
    $labels = [
        'pt_BR' => 'Português',
        'es' => 'Español',
        'en' => 'English',
    ];
@endphp
<div class="flex items-center gap-1 text-xs">
    @foreach(\App\Http\Middleware\SetLocale::LOCALES as $code)
        @php($active = app()->getLocale() === $code)
        <a href="{{ route('locale.switch', $code) }}"
           class="px-2 py-1 rounded transition {{ $active
               ? ($dark ? 'bg-cyan-500 text-slate-950' : 'bg-cyan-500 text-white')
               : ($dark ? 'bg-slate-800 text-slate-300 hover:bg-slate-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300') }}">
            {{ $labels[$code] }}
        </a>
    @endforeach
</div>
