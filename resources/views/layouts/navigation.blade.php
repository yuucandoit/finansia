@php
    $navGroups = [
        'Utama' => [
            [
                'label'  => 'Dashboard',
                'route'  => 'dashboard',
                'active' => request()->routeIs('dashboard'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
            ],
            [
                'label'  => 'Transaksi',
                'route'  => 'transaction.index',
                'active' => request()->routeIs('transaction.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
            ],
            [
                'label'  => 'Saving & Rekening',
                'route'  => 'saving.index',
                'active' => request()->routeIs('saving.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />',
            ],
            [
                'label'  => 'Dompet Bersama',
                'route'  => 'shared-wallet.index',
                'active' => request()->routeIs('shared-wallet.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />',
            ],
        ],
        'Perencanaan' => [
            [
                'label'  => 'Anggaran',
                'route'  => 'budget.index',
                'active' => request()->routeIs('budget.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />',
            ],
            [
                'label'  => 'Future Planner',
                'route'  => 'future-planner.index',
                'active' => request()->routeIs('future-planner.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />',
            ],
            [
                'label'  => 'Aset & Investasi',
                'route'  => 'asset.index',
                'active' => request()->routeIs('asset.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />',
            ],
            [
                'label'  => 'Laporan Keuangan',
                'route'  => 'report.index',
                'active' => request()->routeIs('report.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
            ],
            [
                'label'  => 'Simulasi Proyeksi',
                'route'  => 'simulation.index',
                'active' => request()->routeIs('simulation.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />',
            ],
        ],
        'Otomasi & Kewajiban' => [
            [
                'label'  => 'Transaksi Rutin',
                'route'  => 'recurring.index',
                'active' => request()->routeIs('recurring.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />',
            ],
            [
                'label'  => 'Hutang & Piutang',
                'route'  => 'debt.index',
                'active' => request()->routeIs('debt.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
            ],
            [
                'label'  => 'Kartu Kredit & Cicilan',
                'route'  => 'credit-card.index',
                'active' => request()->routeIs('credit-card.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />',
            ],
            [
                'label'  => 'Kategori',
                'route'  => 'category.index',
                'active' => request()->routeIs('category.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />',
            ],
        ],
    ];
@endphp

<!-- Mobile Top Header (< lg) -->
<header class="lg:hidden sticky top-0 z-30 bg-white border-b border-gray-200 px-4 h-16 flex items-center justify-between shadow-2xs">
    <div class="flex items-center gap-3">
        <button type="button" @click="sidebarOpen = true" class="p-2 -ml-2 rounded-xl text-gray-600 hover:bg-gray-100 focus:outline-none" aria-label="Buka Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="h-8 w-auto">
            <span class="font-bold text-[#234066] text-lg">Finansia</span>
        </a>
    </div>

    <div class="flex items-center gap-1.5">
        <button type="button" onclick="openNotificationCenter()" class="relative p-2 rounded-xl bg-gray-100 text-gray-600 hover:text-indigo-600 transition" title="Pusat Notifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span id="notif-badge-mobile" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-500 rounded-full items-center justify-center">0</span>
        </button>

        <button type="button" onclick="togglePrivacyMode()" class="p-2 rounded-xl bg-gray-100 text-gray-600 hover:text-indigo-600 transition" title="Sensor Nominal (Privacy Mode)">
            <svg data-privacy-icon-show class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg data-privacy-icon-hide class="w-5 h-5 hidden text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        </button>

        <button type="button" onclick="toggleDarkMode()" class="p-2 rounded-xl bg-gray-100 text-gray-600 hover:text-indigo-600 transition" title="Ganti Tema Terang/Gelap">
            <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
            <svg data-theme-icon-sun class="w-5 h-5 hidden text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>
        </button>

        <a href="{{ route('transaction.index', ['add' => 1]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-[#234066] text-white text-xs font-bold rounded-xl shadow-sm">
            <span>+ Catat</span>
        </a>
    </div>
</header>

<!-- Mobile Backdrop -->
<div x-show="sidebarOpen"
     x-transition.opacity
     @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden"
     style="display: none;"></div>

<!-- Fixed Sidebar (Desktop always visible, Mobile slide-over) -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col justify-between transition-transform duration-200 ease-in-out shadow-xl lg:shadow-none">

    <!-- Top Brand + Quick CTA -->
    <div class="flex flex-col flex-1 min-h-0">
        <div class="h-16 px-5 flex items-center justify-between border-b border-gray-100 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('assets/logo/logofinansiabaru.png') }}" alt="Logo" class="h-9 w-auto">
                <span class="font-bold text-[#234066] text-xl tracking-tight">Finansia</span>
            </a>
            <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-gray-600">
                ✕
            </button>
        </div>

        <div class="px-4 pt-4 pb-2 shrink-0">
            <a href="{{ route('transaction.index', ['add' => 1]) }}"
               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#234066] to-indigo-600 hover:from-[#1a304e] hover:to-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Catat Transaksi</span>
            </a>
        </div>

        <!-- Scrollable Menu Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-5">
            @foreach($navGroups as $groupLabel => $items)
                <div>
                    <p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        {{ $groupLabel }}
                    </p>
                    <div class="space-y-1">
                        @foreach($items as $nav)
                            <a href="{{ route($nav['route']) }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $nav['active'] ? 'bg-indigo-50 text-indigo-700 shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     class="w-5 h-5 shrink-0 {{ $nav['active'] ? 'text-indigo-600' : 'text-gray-400' }}">
                                    {!! $nav['icon'] !!}
                                </svg>
                                <span class="truncate">{{ $nav['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(Auth::user()->isMasterMind())
                @php $pendingCount = \App\Models\User::pendingApprovalsCount(); @endphp
                <div>
                    <p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        Admin
                    </p>
                    <a href="{{ route('admin.approvals.index') }}"
                       class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.approvals.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3 truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <span class="truncate">Persetujuan User</span>
                        </span>
                        @if($pendingCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold text-white bg-rose-500 rounded-full animate-pulse">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.saas.index') }}"
                       class="mt-1 flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.saas.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3" />
                        </svg>
                        <span class="truncate">SaaS & Kuota AI</span>
                    </a>
                </div>
            @endif
        </nav>
    </div>

    <!-- Bottom Notification, Privacy, Theme Toggle & User Profile -->
    <div class="p-3 border-t border-gray-100 space-y-2 shrink-0">
        @if(!Auth::user()->isPro())
            <a href="{{ route('pricing.index') }}"
               class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-xs transition-all">
                <span>⚡ Upgrade Finansia Pro</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-white/20">PRO</span>
            </a>
        @endif

        <button type="button"
                onclick="openNotificationCenter()"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all">
            <span class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span>Notifikasi</span>
            </span>
            <span id="notif-badge-desktop" class="hidden text-[10px] px-1.5 py-0.5 rounded-full bg-rose-500 text-white font-bold">0</span>
        </button>

        <button type="button"
                onclick="togglePrivacyMode()"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all">
            <span class="flex items-center gap-2.5">
                <svg data-privacy-icon-show class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg data-privacy-icon-hide class="w-4 h-4 hidden text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <span data-privacy-label>Sensor Nominal</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">Privasi</span>
        </button>

        <button type="button"
                onclick="toggleDarkMode()"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all">
            <span class="flex items-center gap-2.5">
                <svg data-theme-icon-moon class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg data-theme-icon-sun class="w-4 h-4 hidden text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <span data-theme-label>Mode Gelap</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700">Tema</span>
        </button>

        <div class="flex items-center justify-between gap-2 px-2 py-1.5">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-w-0 flex-1 group">
                <div class="w-9 h-9 rounded-full bg-[#234066] text-white font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-800 truncate group-hover:text-indigo-600">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Log Out">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
