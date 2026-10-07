<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Ridgeline Race Operations</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#0f2922', // Warna sidebar hijau gelap
                            light: '#f4f5f0', // Warna background konten
                            card: '#ffffff',
                            accent: '#1b4332'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f4f5f0] text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR BACKEND -->
        <aside class="w-64 bg-[#0f2922] text-gray-300 flex flex-col justify-between hidden md:flex">
            <div>
                <!-- Logo Brand -->
                <div class="p-6 flex items-center space-x-3 border-b border-gray-800">
                    <div class="text-white font-bold text-xl tracking-wider flex items-center">
                        <svg class="w-8 h-8 text-emerald-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        RIDGELINE
                    </div>
                </div>
                <div class="px-6 py-2 text-xs uppercase tracking-wider text-emerald-500 font-semibold">Race Operations</div>

                <!-- Selector / Dropdown Trail Collective -->
                <div class="px-4 py-3">
                    <div class="bg-[#15382e] p-2 rounded-lg flex items-center justify-between text-sm text-white cursor-pointer">
                        <span>Trail Collective</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-4 px-3 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg bg-[#1b4332] text-white font-medium text-sm">
                        <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-[#15382e] text-gray-300 text-sm transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Events
                    </a>
                    <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-[#15382e] text-gray-300 text-sm transition">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Qualifications
                        </div>
                        <span class="bg-amber-500 text-gray-900 font-bold text-xs px-2 py-0.5 rounded-full">5</span>
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-[#15382e] text-gray-300 text-sm transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Participants
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-[#15382e] text-gray-300 text-sm transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Check-in
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-[#15382e] text-gray-300 text-sm transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Results
                    </a>
                </nav>
            </div>

            <!-- User Profile & Logout Footer Sidebar -->
            <div class="p-4 border-t border-gray-800 flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 shrink-0 rounded bg-emerald-600 flex items-center justify-center font-bold text-white text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'Admin', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-xs text-gray-400">Race administrator</div>
                    </div>
                </div>
                
                <!-- Tombol Logout (Menggunakan Form POST ke route /logout) -->
                <form action="{{ url('/logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Keluar Akun" class="text-gray-400 hover:text-red-400 transition cursor-pointer p-1.5 rounded-lg hover:bg-[#15382e]">
                        <!-- Ikon Logout -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- Top Navigation Bar -->
            <header class="bg-[#f4f5f0] border-b border-gray-200 px-8 py-4 flex justify-between items-center text-sm">
                <div class="text-gray-500 flex items-center space-x-2">
                    <span>Workspace</span>
                    <span>></span>
                    <span class="text-gray-800 font-medium">Dashboard</span>
                </div>
                <div class="flex items-center space-x-6 text-gray-600">
                    <span class="text-xs font-semibold bg-gray-200 px-2.5 py-1 rounded">Asia/Bangkok · THB</span>
                    <svg class="w-5 h-5 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <svg class="w-5 h-5 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </header>

            <!-- Dynamic Blade Content -->
            <main class="p-8">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>