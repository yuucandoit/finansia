<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Finansia</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    
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
            
            <div class="mb-5">
                <p class="font-sans text-base md:text-lg text-[#234066] font-semibold">Atur & Catat Cashflow-mu</p>
                <p class="text-xs text-gray-500 mt-1">Platform keuangan cerdas dengan integrasi Asisten AI dan rekonsiliasi mutasi rekening otomatis.</p>
            </div>

            <div class="w-full max-w-[280px] md:max-w-[360px] hidden sm:block">
                <img src="{{ asset('assets/logo/icon_login.png') }}" alt="Login Illustration" class="w-full h-auto drop-shadow-sm">
            </div>
        </section>

        
        <section class="w-full max-w-md bg-white p-7 md:p-8 rounded-3xl shadow-sm border border-gray-100">
            <div class="mb-6 text-left">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Selamat Datang 👋</h2>
                <p class="text-sm text-gray-500 mt-1">Masukkan kredensial untuk mengakses akun Finansia Anda.</p>
            </div>

            
            @if (session('registered_pending'))
                <div class="mb-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-sm">
                    <div class="p-1 rounded-lg bg-amber-100 shrink-0 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="leading-relaxed">
                        <p class="font-bold mb-0.5 text-amber-900">Menunggu Approval Master Mind</p>
                        <p class="text-amber-800">{{ session('registered_pending') }}</p>
                    </div>
                </div>
            @endif

            
            @if (session('status'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                
                <div>
                    <x-input-label for="email" :value="__('Alamat Email')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  placeholder="nama@email.com"
                                  required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
                </div>

                
                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between">
                        <x-input-label for="password" :value="__('Kata Sandi')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    </div>

                    <div class="relative mt-1">
                        <input id="password" 
                               class="block w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm pr-10 shadow-sm"
                               :type="show ? 'text' : 'password'"
                               name="password"
                               placeholder="Masukkan kata sandi"
                               required autocomplete="current-password" />
                        
                        <button type="button" @click="show = !show" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none text-xs">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" />
                </div>

                
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#1566da] shadow-sm focus:ring-[#1566da]" name="remember">
                        <span class="ms-2 text-xs text-gray-600 font-medium select-none">{{ __('Ingat saya') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-xs text-[#1566da] hover:underline font-semibold" href="{{ route('password.request') }}">
                            {{ __('Lupa kata sandi?') }}
                        </a>
                    @endif
                </div>

                
                <button type="submit" class="w-full bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:from-[#1359c2] hover:to-[#3b82e6] text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-blue-100 hover:shadow-lg transition duration-200 mt-2">
                    Masuk ke Akun
                </button>

                
                <div class="pt-4 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Belum memiliki akun?
                        <a href="{{ route('register') }}" class="font-semibold text-[#1566da] hover:underline ml-1">
                            Daftar Sekarang
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
    
