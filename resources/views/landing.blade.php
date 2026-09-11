<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Finansia — Smart Personal Finance & AI Financial Assistant</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fafc] text-gray-800 font-sans antialiased selection:bg-blue-600 selection:text-white">

    
    <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-gray-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Finansia Logo" class="h-10 w-auto group-hover:scale-105 transition duration-200">
                <span class="font-sans text-2xl font-extrabold text-[#234066] tracking-tight">
                    Finansia
                </span>
            </a>

            
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#fitur" class="hover:text-[#1566da] transition">Fitur Utama</a>
                <a href="#ai-assistant" class="hover:text-[#1566da] transition">Asisten AI</a>
                <a href="#cara-kerja" class="hover:text-[#1566da] transition">Cara Kerja</a>
                <a href="#keamanan" class="hover:text-[#1566da] transition">Keamanan</a>
            </nav>

            
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-[#234066] hover:text-[#1566da] px-4 py-2.5 rounded-xl hover:bg-blue-50/60 transition">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:from-[#1359c2] hover:to-[#3b82e6] px-5 py-2.5 rounded-xl shadow-md shadow-blue-200 hover:shadow-lg transition">
                    Daftar Akun
                </a>
            </div>
        </div>
    </header>

    
    <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28">
        
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-gradient-to-tr from-blue-300/30 to-indigo-300/30 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            
            
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold mb-6 shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-ping"></span>
                <span>Didukung Otomasi AI & Dekripsi E-Statement Bank</span>
            </div>

            
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#234066] tracking-tight max-w-4xl mx-auto leading-[1.15]">
                Kelola Keuangan Pribadimu <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-[#1566da] via-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    Lebih Cerdas dengan Sentuhan AI
                </span>
            </h1>

            
            <p class="mt-6 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Catat pengeluaran harian semudah chatting santai, pantau cashflow multi-rekening secara real-time, dan dekripsi mutasi bank resmi tanpa repot buka sandi manual.
            </p>

            
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl bg-gradient-to-r from-[#1566da] to-[#4892ef] hover:from-[#1359c2] hover:to-[#3b82e6] text-white font-bold shadow-lg shadow-blue-200 hover:shadow-xl transition transform hover:-translate-y-0.5">
                    <span>Mulai Sekarang (Gratis)</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl bg-white border border-gray-200 text-[#234066] font-bold hover:bg-gray-50 shadow-sm hover:shadow transition">
                    <span>Sudah Punya Akun? Masuk</span>
                </a>
            </div>

            <div class="mt-4 text-xs text-gray-500 flex items-center justify-center gap-4">
                <span>🛡️ Registrasi aman via approval Master Mind</span>
                <span>•</span>
                <span>⚡ Setup cepat & instan</span>
            </div>

            
            <div class="mt-14 max-w-5xl mx-auto">
                <div class="relative rounded-3xl bg-white p-4 sm:p-6 shadow-2xl border border-gray-200/80">
                    
                    <div class="flex items-center gap-2 pb-4 mb-4 border-b border-gray-100">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="mx-auto text-xs text-gray-400 font-mono bg-gray-50 px-6 py-1 rounded-lg border border-gray-100">
                            https://finansia.app/dashboard
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-left">
                        
                        <div class="lg:col-span-2 space-y-4">
                            
                            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg">
                                <div class="relative z-10">
                                    <span class="text-blue-100 text-xs font-semibold uppercase tracking-wider">Total Saldo Terkonsolidasi</span>
                                    <div class="text-3xl sm:text-4xl font-extrabold mt-1">Rp 24.850.000</div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-white/20">
                                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                            <div class="text-[11px] text-emerald-200 font-medium">Bulan Ini Pemasukan</div>
                                            <div class="text-base font-bold text-white">+Rp 15.000.000</div>
                                        </div>
                                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                            <div class="text-[11px] text-rose-200 font-medium">Bulan Ini Pengeluaran</div>
                                            <div class="text-base font-bold text-white">-Rp 4.250.000</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Rekening & E-Wallet Terhubung</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center">BCA</div>
                                            <div>
                                                <div class="text-xs font-bold text-gray-800">BCA Utama</div>
                                                <div class="text-[10px] text-gray-400">•••• 8821</div>
                                            </div>
                                        </div>
                                        <div class="text-xs font-bold text-gray-700">Rp 18.200.000</div>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs flex items-center justify-center">GOP</div>
                                            <div>
                                                <div class="text-xs font-bold text-gray-800">GoPay Saldo</div>
                                                <div class="text-[10px] text-gray-400">Dompet Digital</div>
                                            </div>
                                        </div>
                                        <div class="text-xs font-bold text-gray-700">Rp 650.000</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="bg-gradient-to-b from-blue-50/50 to-indigo-50/30 rounded-2xl p-4 border border-blue-100 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-blue-100">
                                    <div class="w-7 h-7 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs">
                                        <i class="fa-solid fa-robot"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-800">Finansia AI Assistant</div>
                                        <div class="text-[10px] text-emerald-600 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online & Siap Catat
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="space-y-3 text-xs">
                                    
                                    <div class="bg-blue-600 text-white p-2.5 rounded-2xl rounded-tr-none ml-6 shadow-sm">
                                        "Aku baru abis beli kopi 25rb pake GoPay"
                                    </div>

                                    
                                    <div class="bg-white p-3 rounded-2xl rounded-tl-none border border-gray-200 mr-4 shadow-sm space-y-2">
                                        <div class="text-gray-700 leading-snug">
                                            ✅ Siap! Transaksi terdeteksi:
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded-xl border border-gray-100 text-[11px] space-y-1">
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">Kategori:</span>
                                                <span class="font-semibold text-gray-700">Makanan & Minuman</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">Nominal:</span>
                                                <span class="font-bold text-rose-600">-Rp 25.000</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">Rekening:</span>
                                                <span class="font-semibold text-gray-700">GoPay</span>
                                            </div>
                                        </div>
                                        <button class="w-full py-1 text-[11px] font-bold text-white bg-emerald-600 rounded-lg shadow-sm">
                                            ✓ Konfirmasi & Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-2 border-t border-blue-100 text-[10px] text-center text-gray-400">
                                Powered by OpenRouter AI Engine
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    
    <section id="fitur" class="py-20 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Fitur Unggulan</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#234066] mt-2">
                    Semua yang Kamu Butuhkan untuk Finansial yang Rapi
                </h2>
                <p class="text-gray-600 text-sm sm:text-base mt-3">
                    Finansia menghilangkan kerepotan pencatatan tradisional menjadi pengalaman yang intuitif, cepat, dan otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#1566da] flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-comments-dollar"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Input Transaksi Percakapan</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Cukup ketik transaksi harian seperti *"beli bensin 50rb di Pertamina"*, AI otomatis mengurai nominal, memilih kategori, dan rekening terkait.
                    </p>
                </div>

                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Tanya Keuangan Riil</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Tanyakan kondisi finansialmu ke chatbot: *"Bulan ini jajan makan habis berapa?"*. AI menjawab akurat berdasarkan snapshot data transaksi riil Anda.
                    </p>
                </div>

                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Dekripsi E-Statement Bank</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Upload mutasi rekening Excel terproteksi sandi dari bank resmi. Engine Python di backend otomatis mendekripsi dan memasukkannya ke database.
                    </p>
                </div>

                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Manajemen Multi-Rekening</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Pantau seluruh akun bank (BCA, Mandiri, BRI, BNI) dan e-wallet dalam satu tempat dengan penyesuaian saldo yang aman dan atomik.
                    </p>
                </div>

                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Approval Master Mind</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Akses pengguna dikurasi dan disetujui langsung oleh Master Mind (<strong class="text-gray-800">wahyusnjy</strong>) untuk privasi dan integritas data terbaik.
                    </p>
                </div>

                
                <div class="bg-gray-50/70 hover:bg-white p-7 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center text-xl mb-5">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#234066] mb-2">Performa Tinggi & Modern</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Dibangun dengan <strong>Laravel 13</strong>, <strong>Tailwind CSS</strong>, dan database <strong>PostgreSQL</strong> untuk kecepatan respons tanpa kompromi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    
    <section id="cara-kerja" class="py-20 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Langkah Sederhana</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#234066] mt-2">
                    Cara Mudah Memulai dengan Finansia
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm relative text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto mb-5 shadow-md shadow-blue-200">
                        1
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Daftar Akun Baru</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Isi form registrasi dengan nama dan email Anda. Akun akan diajukan ke antrean verifikasi Master Mind.
                    </p>
                </div>

                
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm relative text-center">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto mb-5 shadow-md shadow-indigo-200">
                        2
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Approval Master Mind</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Master Mind (<span class="font-semibold text-gray-800">wahyusnjy</span>) meninjau dan menyetujui akun Anda agar siap digunakan secara aktif.
                    </p>
                </div>

                
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm relative text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto mb-5 shadow-md shadow-emerald-200">
                        3
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Catat & Tanyakan ke AI</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Login, tambahkan rekening tabunganmu, dan mulai catat transaksi hanya dengan mengetik bahasa santai sehari-hari!
                    </p>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-16 bg-gradient-to-br from-[#234066] to-[#1566da] text-white text-center relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                Siap Merapikan Keuangan Pribadimu?
            </h2>
            <p class="mt-4 text-blue-100 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                Bergabunglah sekarang untuk merasakan kemudahan mencatat transaksi finansial dengan asisten AI yang cerdas dan hemat waktu.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-white text-[#234066] hover:bg-blue-50 font-bold shadow-lg transition">
                    Daftar Sekarang
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold transition">
                    Masuk ke Akun
                </a>
            </div>
        </div>
    </section>

    
    <footer class="bg-white border-t border-gray-100 py-10 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="h-6 w-auto">
                <span class="font-bold text-[#234066] text-sm">Finansia</span>
                <span>— Smart Personal Finance & AI Assistant</span>
            </div>
            <div>
                © {{ date('Y') }} Finansia. Designed & Built by <strong>Wahyusnjy & AI</strong>.
            </div>
        </div>
    </footer>

</body>
</html>
