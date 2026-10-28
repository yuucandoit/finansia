<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Sandi - Finansia</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        
        
        <a href="{{ route('landing') }}" class="flex items-center justify-center gap-2.5 mb-6 group">
            <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="w-12 h-auto group-hover:scale-105 transition duration-200">
            <span class="font-sans text-3xl font-extrabold text-[#234066] tracking-tight">
                Finansia
            </span>
        </a>

        
        <div class="w-full max-w-md bg-white p-7 sm:p-9 rounded-3xl shadow-sm border border-gray-100">
            
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1566da] flex items-center justify-center text-xl mb-5 shadow-sm">
                <i class="fa-solid fa-key"></i>
            </div>

            <div class="mb-5 text-left">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Lupa Kata Sandi?</h2>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Jangan khawatir! Masukkan alamat email akun Finansia Anda, dan kami akan mengirimkan tautan untuk membuat kata sandi baru.
                </p>
            </div>

            
            @if (session('status'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                
                <div>
                    <x-input-label for="email" :value="__('Alamat Email')" class="text-xs font-semibold text-gray-700 uppercase tracking-wider" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-xl border-gray-300 focus:border-[#1566da] focus:ring-[#1566da] text-sm" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  placeholder="nama@email.com" 
                                  required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
                </div>

                
                <button type="submit" class="w-full bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:from-[#1359c2] hover:to-[#3b82e6] text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-blue-100 hover:shadow-lg transition duration-200 mt-2">
                    Kirim Tautan Reset Sandi
                </button>

                
                <div class="pt-4 border-t border-gray-100 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-[#1566da] hover:underline inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Kembali ke Halaman Masuk</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
