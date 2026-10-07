<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · Nusantara Trail</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: { forest: { 900: '#123a2b', 800: '#1a4d38', 700: '#1f5c42', 600: '#2a7352' } }
            } }
        }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @verbatim
    <style type="text/tailwindcss">
        @layer components {
            .card { @apply bg-white rounded-2xl border border-[#e9e7df]; }
            .lbl { @apply block text-[13px] font-semibold text-gray-900 mb-1.5; }
            .inp { @apply w-full h-11 rounded-xl border border-[#e2e0d8] bg-white px-4 text-sm text-gray-900 focus:outline-none focus:border-forest-700 focus:ring-1 focus:ring-forest-700; }
            .btn-primary { @apply inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-forest-700 hover:bg-forest-800 text-white text-sm font-semibold transition-colors; }
            .btn-outline { @apply inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-white border border-[#e2e0d8] hover:bg-gray-50 text-sm font-semibold text-gray-900 transition-colors; }
            .th { @apply px-6 py-3 text-left text-xs font-medium text-gray-500; }
            .td { @apply px-6 py-4 text-sm text-gray-900; }
            .chip { @apply inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold; }
        }
    </style>
    <style>[x-cloak] { display: none !important; }</style>
    @endverbatim
</head>
<body class="font-sans antialiased bg-[#f4f3ee] text-gray-900">

@php
    $nav = [
        ['Dashboard', 'ph-squares-four', '/admin/dashboard', 'admin/dashboard*'],
        ['Events', 'ph-calendar-blank', '/admin/events', 'admin/events*'],
        ['Qualifications', 'ph-seal-check', '#', 'admin/qualifications*'],
        ['Participants', 'ph-users', '#', 'admin/participants*'],
        ['Check-in', 'ph-scan', '#', 'admin/check-in*'],
        ['Results', 'ph-trophy', '#', 'admin/results*'],
    ];
    $initials = strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 2));
@endphp

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 w-[240px] bg-forest-900 text-white flex flex-col">
        <div class="px-5 pt-6 pb-5 flex items-center gap-3">
            <i class="ph ph-mountains text-3xl text-[#b9d9c4]"></i>
            <div>
                <p class="text-[15px] font-bold tracking-wide leading-tight">NUSANTARA TRAIL</p>
                <p class="text-[10px] tracking-widest text-white/60">RACE OPERATIONS</p>
            </div>
        </div>

        <div class="mx-4 mb-5 rounded-lg border border-white/15 px-3 py-2.5 flex items-center gap-2 text-sm">
            <i class="ph ph-tree-evergreen text-lg text-white/70"></i>
            <span>Nusantara Trail Series</span>
        </div>

        <nav class="px-3 space-y-1">
            @foreach($nav as [$label, $icon, $href, $pattern])
                <a href="{{ $href }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is($pattern) ? 'bg-white/10 text-white font-medium' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph {{ $icon }} text-lg"></i>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto px-4 py-4 flex items-center gap-3 border-t border-white/10">
            <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-xs font-bold">{{ $initials }}</div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-white/60">Race administrator</p>
            </div>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" title="Logout" class="text-white/60 hover:text-white">
                    <i class="ph ph-sign-out text-xl"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- KONTEN -->
    <div class="flex-1 ml-[240px] min-w-0">
        <header class="h-14 bg-white border-b border-[#e9e7df] px-10 flex items-center justify-between text-sm">
            <div class="flex items-center gap-2 text-gray-500">
                <span>Workspace</span>
                <i class="ph ph-caret-right text-xs"></i>
                <span class="text-gray-900">@yield('breadcrumb', 'Events')</span>
            </div>
            <div class="flex items-center gap-4 text-gray-500">
                <span>{{ config('app.timezone') }} · IDR</span>
                <i class="ph ph-question text-lg"></i>
                <i class="ph ph-bell text-lg"></i>
            </div>
        </header>

        <main class="px-10 py-8 max-w-[1240px]">
            @if(session('success'))
                <div class="mb-5 rounded-xl bg-[#e3f0e8] border border-[#c7dfd0] text-forest-800 text-sm px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>