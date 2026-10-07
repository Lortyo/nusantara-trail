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

    <div class="flex h-screen w-full overflow-hidden">

        <!-- SISI KIRI -->
        <div class="hidden lg:block lg:w-[55%] relative h-full">
            <img src="/images/bg-login.png"
                 alt="Runners in forest"
                 class="absolute inset-0 w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>

        <!-- SISI KANAN -->
        <div class="w-full lg:w-[45%] h-full flex items-center justify-center p-6 bg-[#f4f5f3] relative">

            <div class="bg-white w-full max-w-[400px] p-7 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 z-10">

                <h2 class="text-[26px] font-black text-gray-900 mb-1.5 tracking-tight">Welcome back</h2>
                <p class="text-[13px] text-gray-500 mb-6 leading-relaxed">Sign in to access the participant portal and prepare for your race.</p>

                <form action="/login" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[12px] px-4 py-2.5">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="email" class="block text-[11px] font-bold text-gray-900 mb-1.5">Participant email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-[11px] font-bold text-gray-900 mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors">
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" value="1"
                                   class="w-4 h-4 rounded border-gray-300 text-[#0c2016] focus:ring-[#0c2016] cursor-pointer">
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
                    No account yet? <a href="/register" class="font-bold text-gray-900 hover:underline">Register here</a>
                </p>

            </div>

        </div>

    </div>

</body>
</html>