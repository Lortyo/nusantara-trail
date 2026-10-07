<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Nusantara Trail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body class="bg-gray-50 flex h-screen font-sans overflow-hidden">

    <!-- SIDEBAR (COMPACT MODE) -->
    <!-- overflow-hidden dipastikan, padding diubah jadi p-4 untuk menghemat ruang -->
    <div
        class="w-[260px] bg-[#0c2016] text-white p-4 flex flex-col justify-between border-r border-[#153123] h-screen overflow-hidden">

        <!-- Bagian Atas -->
        <div>
            <!-- Logo (Ukuran dan margin diperkecil) -->
            <div class="flex items-center gap-2 mb-6 mt-2">
                <div class="bg-[#d2f371] w-10 h-10 rounded-xl flex items-center justify-center text-[#0c2016]">
                    <i class="ph ph-mountain text-2xl font-bold"></i>
                </div>
                <h1 class="font-bold text-[13px] tracking-widest leading-tight">
                    NUSANTARA TRAIL<br>
                    <span class="text-[10px] text-[#d2f371]">SERIES</span>
                </h1>
            </div>

            <!-- Daftar Menu -->
            <p class="text-[10px] text-gray-400 font-bold mb-2 uppercase tracking-wider px-1">Participant Portal</p>
            <!-- space-y-0.5 agar jarak antar menu sangat rapat -->
            <ul class="space-y-0.5 text-[13px] font-medium">

                <li>
                    <!-- Padding menu dikurangi jadi py-2 px-3 -->
                    <a href="/profil"
                        class="{{ Request::is('profil') ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]' }} flex items-center justify-between py-2 px-3 rounded-lg transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="ph ph-user text-lg {{ Request::is('profil') ? 'text-[#d2f371]' : '' }}"></i>
                            <span>Profile</span>
                        </div>
                        @if(Request::is('profil'))
                            <div class="w-1 h-4 bg-orange-500 rounded-full"></div>
                        @endif
                    </a>
                </li>

                <li>
                    <a href="/event-saya"
                        class="{{ Request::is('event-saya') ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]' }} flex items-center justify-between py-2 px-3 rounded-lg transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="ph ph-flag text-lg {{ Request::is('event-saya') ? 'text-[#d2f371]' : '' }}"></i>
                            <span>My events</span>
                        </div>
                        @if(Request::is('event-saya'))
                            <div class="w-1 h-4 bg-orange-500 rounded-full"></div>
                        @endif
                    </a>
                </li>

                <li>
                    <a href="/race-kit"
                        class="{{ Request::is('race-kit') ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]' }} flex items-center justify-between py-2 px-3 rounded-lg transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="ph ph-package text-lg {{ Request::is('race-kit') ? 'text-[#d2f371]' : '' }}"></i>
                            <span>Race kit</span>
                        </div>
                        @if(Request::is('race-kit'))
                            <div class="w-1 h-4 bg-orange-500 rounded-full"></div>
                        @endif
                    </a>
                </li>

                <li>
                    <a href="/change-category"
                        class="{{ Request::is('change-category') ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]' }} flex items-center justify-between py-2 px-3 rounded-lg transition-all">
                        <div class="flex items-center gap-2.5">
                            <i
                                class="ph ph-arrows-left-right text-lg {{ Request::is('change-category') ? 'text-[#d2f371]' : '' }}"></i>
                            <span>Change category</span>
                        </div>
                        @if(Request::is('change-category'))
                            <div class="w-1 h-4 bg-orange-500 rounded-full"></div>
                        @endif
                    </a>
                </li>

                <li>
                    <a href="/events"
                        class="{{ Request::is('events') ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]' }} flex items-center justify-between py-2 px-3 rounded-lg transition-all">
                        <div class="flex items-center gap-2.5">
                            <i
                                class="ph ph-calendar-blank text-lg {{ Request::is('events') ? 'text-[#d2f371]' : '' }}"></i>
                            <span>Events</span>
                        </div>
                        @if(Request::is('events'))
                            <div class="w-1 h-4 bg-orange-500 rounded-full"></div>
                        @endif
                    </a>
                </li>

            </ul>
        </div>

        <!-- Bagian Bawah -->
        <div class="mb-2">
            <!-- Box Bantuan (Padding dan ukuran teks diperkecil) -->
            <div class="bg-[#183626] p-3 rounded-xl mb-3">
                <h3 class="font-bold text-[13px] mb-1 text-white">Need help?</h3>
                <p class="text-[11px] text-gray-300 mb-2 leading-relaxed">Participant support: Mon–Fri,<br>09:00–17:00
                    WIB.</p>
                <a href="mailto:peserta@nusantaratrail.id"
                    class="text-[#d2f371] text-[11px] font-bold hover:underline">peserta@nusantaratrail.id</a>
            </div>

            <!-- User Mini Profile -->
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-[#fde9de] rounded-full"></div>
                    <div>
                        <p class="font-bold text-[13px] text-white leading-tight">Raka Pradipta</p>
                        <p class="text-[10px] text-gray-400">NTS-260184</p>
                    </div>
                </div>
                <form action="/logout" method="POST" class="flex items-center">
                    @csrf
                    <button type="submit" title="Logout" class="text-gray-400 hover:text-white transition-colors">
                        <i class="ph ph-sign-out text-xl"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8 overflow-y-auto">
        @yield('content')
    </div>

</body>

</html>