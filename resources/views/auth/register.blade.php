<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun - Finansia</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row items-center justify-center p-6 gap-8 lg:gap-16">
        
        
        <section class="flex flex-col items-center text-center max-w-md">
            <a href="{{ route('landing') }}" class="flex items-center justify-center gap-2 mb-2 group">
                <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="w-12 md:w-16 group-hover:scale-105 transition duration-200">
                <span class="font-sans text-3xl md:text-4xl font-extrabold text-[#234066] tracking-tight">
                    Finansia
                </span>
            </a>
            
            <div class="mb-4">
                <p class="font-sans text-base md:text-lg text-[#234066] font-medium">Bergabung & Kelola Keuanganmu</p>
                <p class="text-xs text-gray-500 mt-1">Satu akun untuk mencatat transaksi harian, analisis cerdas AI, dan kontrol mutasi rekening.</p>
            </div>

            <div class="w-full max-w-[280px] md:max-w-[360px] hidden sm:block">
                <img src="{{ asset('assets/logo/icon_login.png') }}" alt="Register Illustration" class="w-full h-auto drop-shadow-sm">
            </div>
        </section>

        
        <section class="w-full max-w-md bg-white p-7 md:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="mb-5 text-left">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Daftar Akun Baru</h2>
                <p class="text-sm text-gray-500 mt-1">Lengkapi formulir di bawah ini untuk mengajukan akses akun.</p>
            </div>

            
            <div class="mb-5 p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                <div class="p-1 rounded-lg bg-amber-100 shrink-0 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="leading-relaxed">
                    <strong class="font-semibold text-amber-900">Perhatian:</strong> Pendaftaran akun baru memerlukan persetujuan (*approval*) langsung dari <strong>wahyusnjy</strong> (Master Mind) sebelum akun dapat digunakan untuk masuk.
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                
                <div>
                    <x-input-label for="name" :value="__('Nama Lengkap')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="name" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm" 
                                  type="text" 
                                  name="name" 
                                  :value="old('name')" 
                                  placeholder="Contoh: Budi Santoso"
                                  required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
                </div>

                
                <div>
                    <x-input-label for="email" :value="__('Alamat Email')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  placeholder="nama@email.com"
                                  required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
                </div>

                
                <div>
                    <x-input-label for="password" :value="__('Kata Sandi')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="password" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm"
                                  type="password"
                                  name="password"
                                  placeholder="Minimal 8 karakter"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                </div>

                
                <div>
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm"
                                  type="password"
                                  name="password_confirmation" 
                                  placeholder="Ulangi kata sandi"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs" />
                </div>

                
                <button type="submit" class="w-full bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:from-[#1359c2] hover:to-[#3b82e6] text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-blue-100 hover:shadow-lg transition duration-200 mt-2">
                    Daftar & Ajukan Akses
                </button>

                
                <div class="pt-4 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Sudah memiliki akun?
                        <a href="{{ route('login') }}" class="font-semibold text-[#1566da] hover:underline ml-1">
                            Masuk di Sini
                        </a>
                    </p>
                </div>
            </form>

            
            <div class="mt-4 text-center">
                <a href="{{ route('landing') }}" class="text-xs text-gray-400 hover:text-gray-600 transition inline-flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Halaman Utama</span>
                </a>
            </div>
        </section>
    </div>
</body>
</html>
