<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Finansia</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen flex flex-col lg:flex-row items-center justify-center bg-gray-100 p-6 gap-8 lg:gap-16">
        
        <section class="flex flex-col items-center text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="w-12 md:w-16">
                <span class="font-sans text-3xl md:text-4xl font-bold text-[#234066]">
                    Finansia
                </span>
            </div>
            
            <div class="mb-6">
                <p class="font-sans text-base md:text-lg text-[#234066]">Atur & Catat Cashflow-mu</p>
            </div>

            <div class="w-full max-w-[300px] md:max-w-[400px]">
                <img src="{{ asset('assets/logo/icon_login.png') }}" alt="Login Illustration" class="w-full h-auto">
            </div>
        </section>

        <section class="w-full max-w-md bg-white p-8 rounded-xl shadow-sm lg:shadow-none lg:bg-transparent">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4 mb-2">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:opacity-90 text-white font-semibold py-3 px-4 rounded-lg mt-6 transition duration-200">
                    {{ __('Log in') }}
                </button>

                <div class="flex items-center justify-center mt-6">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 transition" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>
    </div>
</body>
</html>
    
