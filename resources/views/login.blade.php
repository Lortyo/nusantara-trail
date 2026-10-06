<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nusantara Trail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="font-sans antialiased bg-[#f4f5f3]">

    <!-- Container Utama: Fixed 1 Layar Penuh (h-screen) & Overflow Hidden agar tidak bisa scroll -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- SISI KIRI -->
        <div class="hidden lg:block lg:w-[55%] relative h-full">
            <img src="/images/bg-login.png" 
                 alt="Runners in forest" 
                 class="absolute inset-0 w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>

        <!-- SISI KANAN -->
        <!-- overflow-y-auto dihilangkan. Padding luar dikurangi menjadi p-6 agar tidak mentok batas layar -->
        <div class="w-full lg:w-[45%] h-full flex items-center justify-center p-6 bg-[#f4f5f3] relative">
            
            <!-- Kotak Form Putih (Ukuran dikompres agar muat tanpa scroll) -->
            <!-- max-w-[400px] dan p-7 membuat kotak lebih langsing dan hemat tempat -->
            <div class="bg-white w-full max-w-[400px] p-7 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 z-10">
                
                <h2 class="text-[26px] font-black text-gray-900 mb-1.5 tracking-tight">Welcome back</h2>
                <p class="text-[13px] text-gray-500 mb-6 leading-relaxed">Sign in to access the participant portal and prepare for your race.</p>

                <form action="#" method="POST">
                    
                    <div class="mb-4">
                        <label for="email" class="block text-[11px] font-bold text-gray-900 mb-1.5">Participant email</label>
                        <!-- Padding dalam input dikecilkan jadi py-2.5 -->
                        <input type="email" id="email" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors" placeholder="">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-[11px] font-bold text-gray-900 mb-1.5">Password</label>
                        <!-- Padding dalam input dikecilkan jadi py-2.5 -->
                        <input type="password" id="password" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors" placeholder="">
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-[#0c2016] focus:ring-[#0c2016] cursor-pointer">
                            <span class="text-[11px] text-gray-500 group-hover:text-gray-900 transition-colors">Remember me</span>
                        </label>
                        <a href="#" class="text-[11px] font-bold text-gray-900 hover:underline">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full bg-[#0c2016] hover:bg-[#183626] text-white py-3 rounded-xl font-bold text-[13px] transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span>Sign in to portal</span>
                        <i class="ph ph-arrow-right font-bold text-base"></i>
                    </button>

                </form>

                <p class="text-[11px] text-center text-gray-500 mt-5">
                    No account yet? Activate via your registration email.
                </p>

            </div>

        </div>

    </div>

</body>
</html>