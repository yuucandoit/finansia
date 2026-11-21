<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Back Button & Top Action Bar -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <a href="{{ route('saving.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Tabungan
                </a>

                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="openReconcileModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50 rounded-xl text-xs sm:text-sm font-semibold shadow-2xs transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                        </svg>
                        Sesuaikan Saldo Aktual
                    </button>
                </div>
            </div>

            <!-- Rekening Header & Saldo Saat Ini -->
            <div class="bg-white rounded-3xl border border-gray-100 p-5 sm:p-6 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $saving->name }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $saving->bank->name ?? 'Bank / Wallet' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 font-mono mt-1">No. Rek: {{ $saving->account_number }}</p>
                        <p class="text-xs text-indigo-600 font-semibold mt-1.5 flex items-center gap-1.5">
                            <span>📅 Menampilkan periode:</span>
                            <span class="bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">{{ $periodLabel }}</span>
                        </p>
                    </div>
                </div>

                <!-- Saldo Card -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-5 shadow-lg text-white min-w-[260px]">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider">Saldo Rekening Saat Ini</p>
                        <button type="button" onclick="openReconcileModal()" class="text-[11px] text-blue-100 hover:text-white underline">
                            Ubah / Rekonsiliasi
                        </button>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold">Rp {{ number_format($saving->current_balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3 animate-slide-down">
                <div class="w-9 h-9 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-green-800 text-sm font-semibold">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 animate-slide-down">
                <div class="w-9 h-9 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-red-800 text-sm font-semibold">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">✕</button>
            </div>
        @endif

        <!-- =================================================================== -->
        <!-- FILTER PERIODE: HARIAN, MINGGUAN, BULANAN, TAHUNAN, RENTANG TANGGAL -->
        <!-- =================================================================== -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span>🔍 Filter Waktu & Analisis Arus Uang</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih periode untuk melihat uang masuk dan uang keluar kemana saja</p>
                </div>

                <!-- Mode Selector Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar bg-gray-100 p-1 rounded-xl">
                    @php
                        $periodTabs = [
                            'daily'   => 'Harian',
                            'weekly'  => 'Mingguan',
                            'monthly' => 'Bulanan',
                            'yearly'  => 'Tahunan',
                            'range'   => 'Rentang Tanggal',
                        ];
                    @endphp
                    @foreach($periodTabs as $modeKey => $modeLabel)
                        <button type="button"
                                onclick="switchPeriodTab('{{ $modeKey }}')"
                                id="period-tab-{{ $modeKey }}"
                                class="px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all {{ $period === $modeKey ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                            {{ $modeLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Form Filter Dinamis Berdasarkan Mode -->
            <form action="{{ route('saving.detail', $saving->id) }}" method="GET" id="periodFilterForm" class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

                <!-- 1. Input Mode Harian -->
                <div id="filter-box-daily" class="{{ $period === 'daily' ? 'flex' : 'hidden' }} flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tanggal</label>
                        <input type="date" name="date" id="dailyDateInput" value="{{ $selectedDate }}"
                               class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setQuickDaily('{{ now()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            Hari Ini
                        </button>
                        <button type="button" onclick="setQuickDaily('{{ now()->subDay()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            Kemarin
                        </button>
                    </div>
                </div>

                <!-- 2. Input Mode Mingguan -->
                <div id="filter-box-weekly" class="{{ $period === 'weekly' ? 'flex' : 'hidden' }} flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tanggal dalam Minggu</label>
                        <input type="date" name="week_date" id="weeklyDateInput" value="{{ $selectedWeekDate }}"
                               class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setQuickWeekly('{{ now()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            Minggu Ini
                        </button>
                        <button type="button" onclick="setQuickWeekly('{{ now()->subWeek()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            Minggu Lalu
                        </button>
                    </div>
                </div>

                <!-- 3. Input Mode Bulanan -->
                <div id="filter-box-monthly" class="{{ $period === 'monthly' ? 'flex' : 'hidden' }} flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bulan</label>
                        <select name="month" id="monthlyMonthSelect" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            @foreach($monthNames as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ (int)$selectedMonth === (int)$mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun</label>
                        <select name="year" id="monthlyYearSelect" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white" {{ $period !== 'monthly' && $period !== 'yearly' ? 'disabled' : '' }}>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (int)$y === (int)$year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="setQuickMonthly({{ now()->month }}, {{ now()->year }})"
                            class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                        Bulan Ini
                    </button>
                </div>

                <!-- 4. Input Mode Tahunan -->
                <div id="filter-box-yearly" class="{{ $period === 'yearly' ? 'flex' : 'hidden' }} flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tahun</label>
                        <select id="yearlyFilter" class="border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none bg-white"
                                onchange="document.getElementById('monthlyYearSelect').disabled=false; document.getElementById('monthlyYearSelect').value=this.value; document.getElementById('periodFilterForm').submit();">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (int)$y === (int)$year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 5. Input Mode Rentang Tanggal (Range Date) -->
                <div id="filter-box-range" class="{{ $period === 'range' ? 'flex' : 'hidden' }} flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="rangeStartInput" value="{{ $startDate->toDateString() }}"
                               class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="rangeEndInput" value="{{ $endDate->toDateString() }}"
                               class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setQuickRange('{{ now()->subDays(6)->toDateString() }}', '{{ now()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            7 Hari Terakhir
                        </button>
                        <button type="button" onclick="setQuickRange('{{ now()->subDays(29)->toDateString() }}', '{{ now()->toDateString() }}')"
                                class="px-3 py-2 bg-gray-100 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-semibold text-gray-700 transition">
                            30 Hari Terakhir
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-200 transition-all">
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['period', 'date', 'week_date', 'month', 'start_date', 'end_date', 'tx_type', 'search']))
                        <a href="{{ route('saving.detail', $saving->id) }}" class="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- 4 KARTU RINGKASAN PERIODE TERPILIH                                  -->
        <!-- =================================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Uang Masuk Periode Ini -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Uang Masuk</span>
                    <div class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-emerald-700">
                    + Rp {{ number_format($periodIncome, 0, ',', '.') }}
                </p>
                <p class="text-xs text-emerald-600 mt-1">
                    {{ $allPeriodTransactions->filter(fn($t) => $t->balanceEffect() > 0)->count() }} transaksi masuk
                </p>
            </div>

            <!-- 2. Uang Keluar Periode Ini -->
            <div class="bg-gradient-to-br from-rose-50 to-orange-50 border border-rose-100 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Total Uang Keluar</span>
                    <div class="w-8 h-8 bg-rose-600 text-white rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-rose-700">
                    - Rp {{ number_format($periodSpending, 0, ',', '.') }}
                </p>
                <p class="text-xs text-rose-600 mt-1">
                    {{ $allPeriodTransactions->filter(fn($t) => $t->balanceEffect() < 0)->count() }} transaksi keluar
                    @if($periodFeeTotal > 0)
                        • Termasuk fee Rp {{ number_format($periodFeeTotal, 0, ',', '.') }}
                    @endif
                </p>
            </div>

            <!-- 3. Net Cashflow Periode Ini -->
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Selisih Bersih (Net)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $periodNetCashflow >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $periodNetCashflow >= 0 ? 'Surplus' : 'Defisit' }}
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold {{ $periodNetCashflow >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $periodNetCashflow >= 0 ? '+' : '-' }} Rp {{ number_format(abs($periodNetCashflow), 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Selisih masuk & keluar di periode ini
                </p>
            </div>

            <!-- 4. Rata-rata Pengeluaran Harian -->
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rata-rata Keluar / Hari</span>
                    <span class="text-xs font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">{{ $daysInPeriod }} hari</span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-gray-800">
                    Rp {{ number_format($avgDailySpending, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Estimasi laju uang keluar per hari
                </p>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- SECTION ANALITIK: "UANGNYA KEMANA AJA?"                             -->
        <!-- =================================================================== -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                        <span>💸 Lacak Uangnya Kemana Aja</span>
                        <span class="text-xs font-semibold px-2.5 py-0.5 bg-rose-50 text-rose-700 rounded-full border border-rose-200">
                            {{ $periodLabel }}
                        </span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        Peta distribusi pengeluaran, alokasi arus keluar, dan pos yang paling banyak menguras saldo rekening ini.
                    </p>
                </div>
            </div>

            @if($periodSpending > 0)
                <!-- Baris 1: Alokasi Jenis Uang Keluar (4 Kotak Kecil) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-100">
                        <p class="text-[11px] font-bold text-rose-700 uppercase">Belanja / Pengeluaran Murni</p>
                        <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-1">
                            Rp {{ number_format($outflowAllocation['pure_expense'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $periodSpending > 0 ? round(($outflowAllocation['pure_expense'] / $periodSpending) * 100, 1) : 0 }}% dari total keluar
                        </p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-100">
                        <p class="text-[11px] font-bold text-blue-700 uppercase">Transfer ke Rekening Lain</p>
                        <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-1">
                            Rp {{ number_format($outflowAllocation['transfer_out'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $periodSpending > 0 ? round(($outflowAllocation['transfer_out'] / $periodSpending) * 100, 1) : 0 }}% dari total keluar
                        </p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <p class="text-[11px] font-bold text-indigo-700 uppercase">Alokasi ke Tabungan</p>
                        <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-1">
                            Rp {{ number_format($outflowAllocation['saving_out'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $periodSpending > 0 ? round(($outflowAllocation['saving_out'] / $periodSpending) * 100, 1) : 0 }}% dari total keluar
                        </p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-700 uppercase">Biaya Admin / Fee Bank</p>
                        <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-1">
                            Rp {{ number_format($outflowAllocation['fee_total'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $periodSpending > 0 ? round(($outflowAllocation['fee_total'] / $periodSpending) * 100, 1) : 0 }}% dari total keluar
                        </p>
                    </div>
                </div>

                <!-- Baris 2: Breakdown Kategori & Top Pengeluaran -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-2">
                    <!-- Kiri (7 Kolom): Breakdown per Kategori + Chart -->
                    <div class="lg:col-span-7 space-y-4">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">
                            📊 Porsi Pengeluaran per Kategori
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center bg-gray-50/60 p-4 rounded-2xl border border-gray-100">
                            <div class="sm:col-span-5 flex justify-center">
                                <div class="w-44 h-44 relative">
                                    <canvas id="categoryBreakdownChart"></canvas>
                                </div>
                            </div>
                            <div class="sm:col-span-7 space-y-3 max-h-72 overflow-y-auto pr-1">
                                @foreach($spendingByCategory as $catItem)
                                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2 text-xs font-bold text-gray-800">
                                            <span class="truncate">{{ $catItem['category'] }} ({{ $catItem['count'] }}x)</span>
                                            <span class="text-rose-600 shrink-0">Rp {{ number_format($catItem['total'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, max(3, $catItem['percentage'])) }}%"></div>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-gray-400 mt-1">
                                            <span class="truncate">Terbesar: {{ $catItem['top_tx_desc'] }}</span>
                                            <span class="font-semibold text-indigo-600 shrink-0">{{ $catItem['percentage'] }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Kanan (5 Kolom): Top 5 Pengeluaran Terbesar & Pos Paling Sering -->
                    <div class="lg:col-span-5 space-y-5">
                        <!-- Top 5 Pengeluaran Terbesar -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">
                                🔥 5 Pengeluaran Terbesar
                            </h3>
                            <div class="space-y-2">
                                @foreach($topExpenses as $idx => $topTx)
                                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50/80 border border-gray-100">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800 truncate">{{ $topTx->description }}</p>
                                                <p class="text-[11px] text-gray-400">
                                                    {{ \Carbon\Carbon::parse($topTx->transaction_date)->format('d M Y') }} • {{ $topTx->category->name ?? $topTx->type }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-extrabold text-rose-600 shrink-0">
                                            Rp {{ number_format((float)$topTx->amount + (float)($topTx->fee ?? 0), 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Pos Paling Sering Muncul -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">
                                🔄 Pengeluaran Paling Sering Berulang
                            </h3>
                            <div class="space-y-2">
                                @foreach($frequentExpenses as $freq)
                                    <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-amber-50/40 border border-amber-100">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-800 truncate">{{ $freq['description'] }}</p>
                                            <p class="text-[11px] text-amber-700 font-medium">
                                                Muncul {{ $freq['count'] }}x transaksi ({{ $freq['percentage'] }}% dari total keluar)
                                            </p>
                                        </div>
                                        <span class="text-xs font-bold text-gray-800 shrink-0">
                                            Rp {{ number_format($freq['total'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <p class="text-sm font-semibold text-gray-600">Belum ada transaksi uang keluar pada periode {{ $periodLabel }}.</p>
                    <p class="text-xs text-gray-400 mt-1">Coba pilih filter periode lain (Bulanan, Tahunan, atau Rentang Tanggal) di atas.</p>
                </div>
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- DAFTAR RINCIAN TRANSAKSI PADA PERIODE TERPILIH                      -->
        <!-- =================================================================== -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        📋 Daftar Transaksi ({{ $filteredTransactions->count() }})
                    </h2>
                    <p class="text-xs text-gray-500">Rincian mutasi masuk & keluar pada periode {{ $periodLabel }}</p>
                </div>

                <!-- Filter Tipe & Pencarian di Dalam Periode -->
                <form action="{{ route('saving.detail', $saving->id) }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <input type="hidden" name="week_date" value="{{ $selectedWeekDate }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="start_date" value="{{ $startDate->toDateString() }}">
                    <input type="hidden" name="end_date" value="{{ $endDate->toDateString() }}">

                    <select name="tx_type" onchange="this.form.submit()" class="border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="" {{ empty($typeFilter) ? 'selected' : '' }}>Semua Arus (Masuk & Keluar)</option>
                        <option value="out" {{ $typeFilter === 'out' ? 'selected' : '' }}>Hanya Uang Keluar (-)</option>
                        <option value="in" {{ $typeFilter === 'in' ? 'selected' : '' }}>Hanya Uang Masuk (+)</option>
                    </select>

                    <div class="flex items-center gap-1.5">
                        <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari deskripsi/kategori..."
                               class="border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="submit" class="px-3.5 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-semibold transition">
                            Cari
                        </button>
                    </div>
                </form>
            </div>

            @if($filteredTransactions->isNotEmpty())
                @php $lastGroupDate = null; @endphp
                <div class="space-y-2.5">
                    @foreach($filteredTransactions as $tx)
                        @php
                            $txDateFormatted = \Carbon\Carbon::parse($tx->transaction_date)->format('d F Y');
                            $isInflow = $tx->balanceEffect() > 0;
                        @endphp

                        @if($txDateFormatted !== $lastGroupDate)
                            <div class="pt-3 pb-1 flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $txDateFormatted }}</span>
                            </div>
                            @php $lastGroupDate = $txDateFormatted; @endphp
                        @endif

                        <div class="group flex items-center justify-between gap-3 p-3.5 rounded-2xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/10 transition-all">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $isInflow ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                    <span class="font-bold text-sm">{{ $isInflow ? '↓' : '↑' }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $tx->description }}</p>
                                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $isInflow ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $tx->type }}
                                        </span>
                                        @if($tx->category)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                                {{ $tx->category->name }}
                                            </span>
                                        @endif
                                        @if(($tx->fee ?? 0) > 0)
                                            <span class="text-[11px] text-amber-600 font-medium">
                                                • Fee: Rp {{ number_format($tx->fee, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right">
                                    <p class="text-sm sm:text-base font-extrabold {{ $isInflow ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isInflow ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </p>
                                </div>

                                @php
                                    $txEditData = [
                                        'id' => $tx->id,
                                        'description' => $tx->description,
                                        'type' => $tx->type,
                                        'amount' => (float) $tx->amount,
                                        'fee' => (float) ($tx->fee ?? 0),
                                        'savings_id' => $tx->savings_id,
                                        'category_id' => $tx->category_id,
                                        'budget_item_id' => $tx->budget_item_id,
                                        'transaction_date' => \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d'),
                                    ];
                                @endphp
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                            onclick='openEditTxModal(@json($txEditData))'
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit Transaksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                            onclick="openDeleteTxModal({{ $tx->id }}, {{ json_encode($tx->description) }}, '{{ number_format($tx->amount, 0, ',', '.') }}')"
                                            class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Hapus Transaksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 text-gray-400 text-sm">
                    Tidak ada transaksi yang cocok pada periode ini.
                </div>
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- GRAFIK TREND SALDO & PEMASUKAN VS PENGELUARAN TAHUNAN               -->
        <!-- =================================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-5">
                <h3 class="text-base font-bold text-gray-800 mb-4 text-center">Trend Saldo ({{ $year }})</h3>
                <div class="h-72">
                    <canvas id="trendSaldo"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-5">
                <h3 class="text-base font-bold text-gray-800 mb-4 text-center">Pemasukan vs Pengeluaran ({{ $year }})</h3>
                <div class="h-72">
                    <canvas id="income_vs_spend"></canvas>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Rekonsiliasi / Penyesuaian Saldo Aktual -->
    <div id="modal-reconcile" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeReconcileModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Sesuaikan Saldo Aktual</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Samakan saldo aplikasi dengan saldo riil m-banking/dompet</p>
                    </div>
                    <button type="button" onclick="closeReconcileModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form action="{{ route('saving.reconcile', $saving->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Saldo Tercatat Saat Ini:</span>
                        <span class="text-sm font-bold text-gray-800">Rp {{ number_format($saving->current_balance, 0, ',', '.') }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Saldo Aktual Sebenarnya (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="actual_balance" id="reconcileActualInput" required min="0" step="0.01"
                               value="{{ (float) $saving->current_balance }}"
                               oninput="updateReconcilePreview()"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div id="reconcileDiffBox" class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-xs text-blue-800">
                        Masukkan nominal saldo asli di bank untuk melihat selisih penyesuaian.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Catatan Penyesuaian (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Selisih biaya admin / lupa catat parkir"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeReconcileModal()" class="flex-1 py-3 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-200">
                            Simpan Rekonsiliasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Transaksi -->
    <div id="modal-edit" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleTxModal('modal-edit')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Edit Transaksi</h3>
                        <p class="text-xs text-gray-500">Saldo rekening akan otomatis disesuaikan</p>
                    </div>
                    <button type="button" onclick="toggleTxModal('modal-edit')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form id="form-edit-tx" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe</label>
                            <select name="type" id="edit-type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                <option value="Cash Out">Pengeluaran (Cash Out)</option>
                                <option value="Cash In">Pemasukan (Cash In)</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Tabungan">Tabungan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                            <input type="date" name="transaction_date" id="edit-date" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi</label>
                        <input type="text" name="description" id="edit-description" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nominal (Rp)</label>
                            <input type="number" name="amount" id="edit-amount" min="0" step="0.01" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Biaya Admin / Fee</label>
                            <input type="number" name="fee" id="edit-fee" min="0" step="0.01" placeholder="0" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rekening</label>
                            <select name="savings_id" id="edit-savings-id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                @foreach($allUserSavings as $sItem)
                                    <option value="{{ $sItem->id }}">{{ $sItem->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori</label>
                            <select name="category_id" id="edit-category-id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="toggleTxModal('modal-edit')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Transaksi -->
    <div id="modal-delete-tx" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleTxModal('modal-delete-tx')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-center align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Hapus Transaksi Ini?</h3>
                <p class="text-sm text-gray-500 mb-3">
                    <strong id="delete-tx-desc" class="text-gray-800"></strong> (<strong id="delete-tx-amount" class="text-rose-600"></strong>)
                </p>
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-2.5 mb-5">
                    Saldo rekening akan otomatis dikembalikan.
                </p>

                <form id="form-delete-tx" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="toggleTxModal('modal-delete-tx')" class="flex-1 py-2.5 px-4 bg-gray-100 rounded-xl text-sm font-semibold text-gray-700">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const CURRENT_SAVING_BALANCE = {{ (float) $saving->current_balance }};

    function switchPeriodTab(mode) {
        document.getElementById('periodInput').value = mode;
        const modes = ['daily', 'weekly', 'monthly', 'yearly', 'range'];
        modes.forEach(m => {
            const box = document.getElementById('filter-box-' + m);
            const tab = document.getElementById('period-tab-' + m);
            if (box) {
                if (m === mode) {
                    box.classList.remove('hidden');
                    box.classList.add('flex');
                } else {
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                }
            }
            if (tab) {
                if (m === mode) {
                    tab.className = 'px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all bg-white text-blue-600 shadow-2xs';
                } else {
                    tab.className = 'px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all text-gray-600 hover:text-gray-900';
                }
            }
        });

        const monthYearSelect = document.getElementById('monthlyYearSelect');
        if (monthYearSelect) {
            monthYearSelect.disabled = (mode !== 'monthly' && mode !== 'yearly');
        }
    }

    function setQuickDaily(dateStr) {
        document.getElementById('dailyDateInput').value = dateStr;
        document.getElementById('periodFilterForm').submit();
    }

    function setQuickWeekly(dateStr) {
        document.getElementById('weeklyDateInput').value = dateStr;
        document.getElementById('periodFilterForm').submit();
    }

    function setQuickMonthly(month, year) {
        document.getElementById('monthlyMonthSelect').value = month;
        const yrSelect = document.getElementById('monthlyYearSelect');
        if (yrSelect) {
            yrSelect.disabled = false;
            yrSelect.value = year;
        }
        document.getElementById('periodFilterForm').submit();
    }

    function setQuickRange(startStr, endStr) {
        document.getElementById('rangeStartInput').value = startStr;
        document.getElementById('rangeEndInput').value = endStr;
        document.getElementById('periodFilterForm').submit();
    }

    function openReconcileModal() {
        document.getElementById('modal-reconcile').classList.remove('hidden');
        updateReconcilePreview();
    }

    function closeReconcileModal() {
        document.getElementById('modal-reconcile').classList.add('hidden');
    }

    function updateReconcilePreview() {
        const actual = parseFloat(document.getElementById('reconcileActualInput').value) || 0;
        const diff = actual - CURRENT_SAVING_BALANCE;
        const box = document.getElementById('reconcileDiffBox');

        if (Math.abs(diff) < 0.01) {
            box.className = 'p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600';
            box.textContent = 'Saldo aktual sama dengan saldo tercatat (Tidak ada selisih).';
        } else if (diff > 0) {
            box.className = 'p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-semibold';
            box.textContent = 'Akan dicatat transaksi Pemasukan (Penyesuaian +) sebesar Rp ' + Math.abs(diff).toLocaleString('id-ID');
        } else {
            box.className = 'p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-semibold';
            box.textContent = 'Akan dicatat transaksi Pengeluaran (Penyesuaian -) sebesar Rp ' + Math.abs(diff).toLocaleString('id-ID');
        }
    }

    function toggleTxModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openEditTxModal(tx) {
        const form = document.getElementById('form-edit-tx');
        form.action = '/transaksi/' + tx.id;
        document.getElementById('edit-type').value = tx.type || 'Cash Out';
        document.getElementById('edit-date').value = tx.transaction_date || '';
        document.getElementById('edit-description').value = tx.description || '';
        document.getElementById('edit-amount').value = tx.amount || 0;
        document.getElementById('edit-fee').value = tx.fee || '';
        document.getElementById('edit-savings-id').value = tx.savings_id || '';
        document.getElementById('edit-category-id').value = tx.category_id || '';
        document.getElementById('modal-edit').classList.remove('hidden');
    }

    function openDeleteTxModal(id, description, formattedAmount) {
        const form = document.getElementById('form-delete-tx');
        form.action = '/transaksi/' + id;
        document.getElementById('delete-tx-desc').textContent = description;
        document.getElementById('delete-tx-amount').textContent = 'Rp ' + formattedAmount;
        document.getElementById('modal-delete-tx').classList.remove('hidden');
    }

    // Doughnut Chart Kategori Pengeluaran
    document.addEventListener('DOMContentLoaded', function() {
        const catData = @json($spendingByCategory);
        const catCanvas = document.getElementById('categoryBreakdownChart');
        if (catCanvas && catData && catData.length > 0) {
            new Chart(catCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: catData.map(c => c.category),
                    datasets: [{
                        data: catData.map(c => c.total),
                        backgroundColor: [
                            '#4F46E5', '#E11D48', '#F59E0B', '#10B981', '#3B82F6',
                            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#64748B'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });

    // Trend Saldo Tahunan
    (function() {
        const rawData = @json($income_vs_spending);
        let runningBalance = parseFloat(@json($trend_saldo));
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const trendData = Array(12).fill(null);

        const monthlyStats = {};
        rawData.forEach(item => {
            monthlyStats[parseInt(item.month)] = {
                net: parseFloat(item.income) - parseFloat(item.spending)
            };
        });

        for (let i = 12; i >= 1; i--) {
            trendData[i - 1] = runningBalance;
            if (monthlyStats[i]) {
                runningBalance -= monthlyStats[i].net;
            }
        }

        const ctx = document.getElementById('trendSaldo');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Trend Saldo',
                        data: trendData,
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    })();

    // Income vs Spending Tahunan
    (function() {
        const ivsRawData = @json($income_vs_spending);
        const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const incomeData = Array(12).fill(0);
        const spendingData = Array(12).fill(0);

        ivsRawData.forEach(item => {
            const monthIndex = parseInt(item.month) - 1;
            incomeData[monthIndex] = parseFloat(item.income);
            spendingData[monthIndex] = parseFloat(item.spending);
        });

        const ivsCtx = document.getElementById('income_vs_spend');
        if (ivsCtx) {
            new Chart(ivsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [
                        {
                            label: 'Income',
                            data: incomeData,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Spending',
                            data: spendingData,
                            borderColor: 'rgb(244, 63, 94)',
                            backgroundColor: 'rgba(244, 63, 94, 0.15)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + value.toLocaleString('id-ID') } }
                    }
                }
            });
        }
    })();
</script>
