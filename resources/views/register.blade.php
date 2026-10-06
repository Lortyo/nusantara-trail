<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Nusantara Trail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="font-sans antialiased bg-[#f4f5f3]">

    <!-- Container Utama: Full Screen & Flexbox -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- SISI KIRI: Gambar Penuh (Lebar 55%) -->
        <div class="hidden lg:block lg:w-[45%] xl:w-[50%] relative h-full">
            <img src="/images/bg-login.png" 
                 alt="Runners in forest" 
                 class="absolute inset-0 w-full h-full object-cover object-center">
            
            <!-- Overlay gelap agak tebal agar teks terbaca -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Teks Promosi di Atas Gambar (Kiri Bawah) -->
            <div class="absolute bottom-12 left-12 right-12 z-10 text-white">
                <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest mb-3">Participant Portal</p>
                <h1 class="text-4xl font-black mb-4 leading-tight tracking-tight text-white">Your first step to the starting line.</h1>
                <p class="text-sm text-gray-200 leading-relaxed max-w-md">Create an account to discover races, manage registrations, and get ready for your adventure.</p>
            </div>
        </div>

        <!-- SISI KANAN: Form Register (Lebar sisanya) -->
        <!-- overflow-y-auto diaktifkan kembali agar form yang panjang bisa di-scroll -->
        <div class="w-full lg:w-[55%] xl:w-[50%] h-full p-8 lg:p-12 bg-[#f4f5f3] overflow-y-auto">
            
            <div class="max-w-[540px] mx-auto bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 my-8">
                
                <h2 class="text-[28px] font-black text-gray-900 mb-2 tracking-tight">Create your account</h2>
                <p class="text-sm text-gray-500 mb-8 leading-relaxed">Your adventure starts here. Enter your details to join and access the participant portal.</p>

                <form action="#" method="POST">
                    
                    <!-- Baris 1: First name & Last name -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">First name</label>
                            <input type="text" placeholder="First name" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Last name</label>
                            <input type="text" placeholder="Last name" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                        </div>
                    </div>

                    <!-- Baris 2: Email -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Email</label>
                        <input type="email" placeholder="name@email.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                    </div>

                    <!-- Baris 3: Phone number -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Phone number</label>
                        <div class="flex">
                            <select class="border border-gray-200 rounded-l-xl px-3 py-3 text-sm bg-gray-50 focus:outline-none focus:border-[#0c2016] border-r-0">
                                <option>+62</option>
                            </select>
                            <input type="tel" placeholder="812 3456 7890" class="w-full border border-gray-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                        </div>
                    </div>

                    <!-- Baris 4: Nationality & Date of birth -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Nationality</label>
                            <div class="relative">
                                <select class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] appearance-none text-gray-400 bg-white">
                                    <option value="" disabled selected>Select nationality</option>
                                    <option value="id" class="text-gray-900">Indonesia</option>
                                    <option value="my" class="text-gray-900">Malaysia</option>
                                </select>
                                <i class="ph ph-caret-down absolute right-4 top-3.5 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Date of birth</label>
                            <div class="relative">
                                <input type="text" placeholder="DD / MM / YYYY" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                                <i class="ph ph-calendar-blank absolute right-4 top-3.5 text-gray-400 pointer-events-none text-base"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Baris 5: Gender -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Gender</label>
                        <div class="relative">
                            <select class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] appearance-none text-gray-400 bg-white">
                                <option value="" disabled selected>Select gender</option>
                                <option value="male" class="text-gray-900">Male</option>
                                <option value="female" class="text-gray-900">Female</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-4 top-3.5 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Baris 6: Passwords -->
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Password</label>
                            <div class="relative">
                                <input type="password" placeholder="Create a password" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                                <button type="button" class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i class="ph ph-eye-slash text-base"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-900 mb-1.5">Confirm password</label>
                            <div class="relative">
                                <input type="password" placeholder="Re-enter password" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400">
                                <button type="button" class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i class="ph ph-eye-slash text-base"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-500 mb-8">Use at least 8 characters, including letters and numbers.</p>

                    <!-- Persetujuan -->
                    <p class="text-[11px] text-gray-500 mb-4 text-center">
                        By signing up, you agree to our Terms & Conditions and Privacy Policy.
                    </p>

                    <!-- Tombol Create Account -->
                    <button type="submit" class="w-full bg-[#0c2016] hover:bg-[#183626] text-white py-3.5 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2 shadow-sm mb-6">
                        <span>Create account</span>
                        <i class="ph ph-arrow-right font-bold text-base"></i>
                    </button>

                    <!-- Link Sign In -->
                    <p class="text-[11px] text-center text-gray-500">
                        Already have an account? <a href="/login" class="text-gray-900 font-bold hover:underline">Sign in</a>
                    </p>

                </form>

            </div>

        </div>

    </div>

</body>
</html>