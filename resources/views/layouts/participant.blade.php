<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal') · Nusantara Trail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: { ink: '#0c2016', leaf: '#1d4a35', lime: '#d6f26b', ember: '#f26a2a' }
        } } }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @verbatim
    <style type="text/tailwindcss">
        @layer components {
            .eyebrow { @apply text-[11px] font-bold uppercase tracking-wider text-ember; }
            .pcard { @apply bg-white rounded-3xl border border-black/5 shadow-[0_6px_24px_rgba(12,32,22,0.06)]; }
            .btn-dark { @apply inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-ink hover:bg-leaf text-white text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
            .btn-ghost { @apply inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-ink text-sm font-semibold; }
            .chip { @apply inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold; }
        }
    </style>
    @endverbatim
</head>
<body class="font-sans antialiased bg-[#f4f5f0] text-ink">
@php
    $nav = [
        ['Profile', 'ph-user', '/profil', 'profil*'],
        ['My events', 'ph-flag', '/event-saya', 'event-saya*'],
        ['Race kit', 'ph-package', '/race-kit', 'race-kit*'],
        ['Change category', 'ph-arrows-left-right', '/change-category', 'change-category*'],
        ['Events', 'ph-calendar-dots', '/events', 'events*'],
    ];
    $u = auth()->user();
    $code = 'NTS-' . strtoupper(substr((string) $u->getKey(), -6));
@endphp

<aside class="fixed inset-y-0 left-0 w-[264px] bg-ink text-white flex flex-col px-5 py-6">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-11 h-11 rounded-xl bg-lime flex items-center justify-center"><i class="ph ph-mountains text-2xl text-ink"></i></div>
        <div>
            <p class="font-extrabold tracking-wide leading-tight">NUSANTARA TRAIL</p>
            <p class="text-xs font-bold text-lime">SERIES</p>
        </div>
    </div>

    <p class="text-[10px] font-bold tracking-widest text-white/50 mb-3">PARTICIPANT PORTAL</p>
    <nav class="space-y-1.5">
        @foreach($nav as [$label, $icon, $href, $pattern])
            @php $active = request()->is($pattern); @endphp
            <a href="{{ $href }}" class="flex items-center gap-3.5 h-12 px-4 rounded-xl text-[15px] transition-colors {{ $active ? 'bg-[#1c4733] font-bold' : 'text-white/80 hover:bg-white/5' }}">
                <i class="ph {{ $icon }} text-xl {{ $active ? 'text-lime' : '' }}"></i>
                <span>{{ $label }}</span>
                @if($active)<span class="w-1 h-5 rounded-full bg-ember"></span>@endif
            </a>
        @endforeach
    </nav>

    <div class="mt-auto">
        <div class="rounded-2xl bg-white/5 p-4 mb-4 text-sm">
            <p class="font-bold">Need help?</p>
            <p class="text-white/60 text-xs mt-1.5">Participant support: Mon–Fri, 09:00–17:00 WIB.</p>
            <p class="text-lime font-semibold text-sm mt-2">peserta@nusantaratrail.id</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#fbeee6]"></div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-sm truncate">{{ $u->name }}</p>
                <p class="text-xs text-white/50">{{ $code }}</p>
            </div>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" title="Logout" class="text-white/60 hover:text-white"><i class="ph ph-sign-out text-xl"></i></button>
            </form>
        </div>
    </div>
</aside>

<main class="ml-[264px] px-12 py-10">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="eyebrow">@yield('eyebrow')</p>
            <h1 class="text-[34px] font-extrabold tracking-tight leading-tight mt-1">@yield('heading')</h1>
            <p class="text-gray-500 mt-1">@yield('subtitle')</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-4 py-2 text-sm font-bold shrink-0">
            <span class="w-2 h-2 rounded-full bg-[#1f8f55]"></span> @yield('pill', 'Nusantara Trail Series')
        </span>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl bg-[#e3f1e7] border border-[#c5e2cd] text-[#1d6b3b] text-sm font-semibold px-5 py-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm px-5 py-3">
            <ul class="list-disc pl-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mt-8">@yield('content')</div>
</main>

</body>
</html>