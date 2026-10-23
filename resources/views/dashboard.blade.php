<x-app-layout>
    <div class="py-4 sm:py-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8">
            
            <!-- Header Section - Total Balance -->
            <div class="mb-6">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl p-5 sm:p-8 text-white relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 sm:mb-6">
                            <div>
                                <div class="flex items-center gap-2 mb-1 sm:mb-1.5">
                                    <p class="text-blue-100 text-xs sm:text-sm font-medium">Total Kekayaan Bersih</p>
                                    @if(($dashboard['net_worth']['change_pct'] ?? null) !== null)
                                        <span class="text-[10px] sm:text-[11px] px-2 py-0.5 rounded-full font-bold {{ $dashboard['net_worth']['change'] >= 0 ? 'bg-green-500/25 text-green-200 border border-green-400/30' : 'bg-red-500/25 text-red-200 border border-red-400/30' }}">
                                            {{ $dashboard['net_worth']['change'] >= 0 ? '▲ +' : '▼ ' }}{{ number_format(abs($dashboard['net_worth']['change']), 0, ',', '.') }} ({{ $dashboard['net_worth']['change_pct'] }}%) vs awal bulan
                                        </span>
                                    @endif
                                </div>
                                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight flex items-center flex-wrap gap-2">
                                    <span class="balance-amount break-all">
                                         Rp ••••••••
                                    </span>
                                    <span class="showing-btn w-5 h-5 sm:w-6 sm:h-6 text-white/70 hover:text-white cursor-pointer transition-colors" title="Tampilkan/Sembunyikan Saldo">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </h2>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="button" onclick="openPdfImportModal()" class="flex-1 sm:flex-none justify-center bg-white text-blue-700 hover:bg-blue-50 py-2 sm:py-2.5 px-3.5 sm:px-5 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 shadow-md hover:shadow-xl flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Import Transaksi
                                </button>
                                <button onclick="window.location.reload()" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 border border-white/30 text-white py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 shadow-md hover:shadow-xl flex items-center justify-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <span class="hidden xs:inline sm:inline">Refresh</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
                            <!-- Cash In Card -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 sm:p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 bg-green-400/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-green-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-blue-100 text-[11px] sm:text-xs font-medium">Pemasukan Bulan Ini</p>
                                        <p class="text-white font-bold text-base sm:text-lg mt-0.5 truncate">Rp {{ number_format($cashInThisMonth ?? 0, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-blue-200 mt-0.5 truncate">
                                            @if(($dashboard['cashflow']['income']['change_pct'] ?? null) !== null)
                                                <span class="{{ $dashboard['cashflow']['income']['change'] >= 0 ? 'text-green-300 font-bold' : 'text-yellow-300 font-bold' }}">
                                                    {{ $dashboard['cashflow']['income']['change'] >= 0 ? '▲ +' : '▼ ' }}{{ $dashboard['cashflow']['income']['change_pct'] }}%
                                                </span> vs bln lalu
                                            @else
                                                Arus kas murni
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cash Out Card -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 sm:p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 bg-red-400/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-red-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-blue-100 text-[11px] sm:text-xs font-medium">Pengeluaran Bulan Ini</p>
                                        <p class="text-white font-bold text-base sm:text-lg mt-0.5 truncate">Rp {{ number_format($cashOutThisMonth ?? 0, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-blue-200 mt-0.5 truncate">
                                            @if(($dashboard['cashflow']['expense']['change_pct'] ?? null) !== null)
                                                <span class="{{ $dashboard['cashflow']['expense']['change'] <= 0 ? 'text-green-300 font-bold' : 'text-red-300 font-bold' }}">
                                                    {{ $dashboard['cashflow']['expense']['change'] >= 0 ? '▲ +' : '▼ ' }}{{ $dashboard['cashflow']['expense']['change_pct'] }}%
                                                </span> vs bln lalu
                                            @else
                                                Termasuk admin & fee
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Net Cash Flow Card -->
                            @php
                                $netAmount = $dashboard['cashflow']['net']['amount'] ?? ($cashInThisMonth - $cashOutThisMonth);
                                $isSurplus = $netAmount >= 0;
                            @endphp
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 sm:p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 {{ $isSurplus ? 'bg-emerald-400/30 text-emerald-200' : 'bg-amber-400/30 text-amber-200' }} rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-blue-100 text-[11px] sm:text-xs font-medium">Arus Kas Bersih</p>
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase {{ $isSurplus ? 'bg-emerald-500/30 text-emerald-200' : 'bg-red-500/30 text-red-200' }}">
                                                {{ $isSurplus ? 'Surplus' : 'Defisit' }}
                                            </span>
                                        </div>
                                        <p class="text-white font-bold text-base sm:text-lg mt-0.5 truncate">
                                            {{ $isSurplus ? '+' : '' }}Rp {{ number_format($netAmount, 0, ',', '.') }}
                                        </p>
                                        <p class="text-[10px] text-blue-200 mt-0.5 truncate">Pemasukan − Pengeluaran</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Savings Rate Card -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 sm:p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 bg-indigo-400/30 rounded-xl flex items-center justify-center flex-shrink-0 text-indigo-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-blue-100 text-[11px] sm:text-xs font-medium">Tingkat Tabungan</p>
                                        <p class="text-white font-bold text-base sm:text-lg mt-0.5 truncate">
                                            {{ ($dashboard['cashflow']['savings_rate'] ?? null) !== null ? $dashboard['cashflow']['savings_rate'] . '%' : '-' }}
                                        </p>
                                        <p class="text-[10px] text-blue-200 mt-0.5 truncate">
                                            @if(($dashboard['cashflow']['internal_transfer'] ?? 0) > 0)
                                                Pindah rekening: Rp {{ number_format($dashboard['cashflow']['internal_transfer'], 0, ',', '.') }}
                                            @else
                                                Rasio kas tersimpan
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uncategorized Warning Alert (Jika ada transaksi tanpa kategori) -->
            @if(($dashboard['uncategorized']['count'] ?? 0) > 0)
                <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900">{{ $dashboard['uncategorized']['count'] }} Transaksi Belum Memiliki Kategori</h4>
                            <p class="text-xs text-amber-700">Total nominal Rp {{ number_format($dashboard['uncategorized']['amount'], 0, ',', '.') }} belum dikategorikan. Memberi kategori membantu visualisasi ke mana uang Anda pergi.</p>
                        </div>
                    </div>
                    <a href="{{ route('transaction.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-colors whitespace-nowrap shadow-sm">
                        Beri Kategori Sekarang →
                    </a>
                </div>
            @endif

            <!-- Grid Rekening & Ringkasan Anggaran -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                <!-- Rekening Tabungan User -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-800">Saldo per Rekening</h3>
                            <p class="text-xs text-gray-500">Pergerakan dana dan likuiditas di tiap kantong</p>
                        </div>
                        <a href="{{ route('saving.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                            Kelola Rekening →
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @forelse(($dashboard['accounts'] ?? []) as $acc)
                            <div class="p-3.5 bg-gray-50 hover:bg-blue-50/40 rounded-xl border border-gray-100 transition-colors">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase">{{ $acc['bank'] ?? 'Bank' }}</span>
                                    <span class="text-[10px] font-bold {{ ($acc['change_this_month'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ ($acc['change_this_month'] ?? 0) >= 0 ? '+' : '' }}Rp {{ number_format($acc['change_this_month'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $acc['name'] }}</p>
                                <p class="text-sm sm:text-base font-extrabold text-gray-900 mt-0.5">
                                    Rp {{ number_format($acc['balance'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 col-span-3 py-3 text-center">Belum ada rekening terdaftar.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Ringkasan Anggaran Pos -->
                <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-800">Anggaran Pos</h3>
                                <p class="text-xs text-gray-500">{{ $dashboard['period']['label'] ?? 'Bulan Ini' }}</p>
                            </div>
                            <span class="text-[10px] sm:text-[11px] px-2.5 py-0.5 rounded-full font-extrabold uppercase {{ ($dashboard['budget']['status'] ?? 'safe') === 'safe' ? 'bg-green-100 text-green-700' : (($dashboard['budget']['status'] ?? '') === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
                                {{ ($dashboard['budget']['status'] ?? 'safe') === 'safe' ? 'Aman' : (($dashboard['budget']['status'] ?? '') === 'warning' ? 'Waspada' : 'Overbudget') }}
                            </span>
                        </div>
                        @if(($dashboard['budget']['has_budget'] ?? false))
                            <div class="space-y-2 mt-2">
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Terpakai ({{ $dashboard['budget']['percentage'] }}%)</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($dashboard['budget']['spent'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full {{ ($dashboard['budget']['status'] ?? '') === 'safe' ? 'bg-green-500' : (($dashboard['budget']['status'] ?? '') === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ min(100, $dashboard['budget']['percentage']) }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[11px] text-gray-500 pt-1">
                                    <span>Total Plafon: <strong class="text-gray-700">Rp {{ number_format($dashboard['budget']['total'], 0, ',', '.') }}</strong></span>
                                    <span>Sisa: <strong class="{{ $dashboard['budget']['remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($dashboard['budget']['remaining'], 0, ',', '.') }}</strong></span>
                                </div>
                            </div>
                        @else
                            <div class="py-3 text-center">
                                <p class="text-xs text-gray-400">Belum ada anggaran pos yang ditetapkan untuk bulan ini.</p>
                                <a href="{{ route('budget.index') }}" class="inline-block mt-3 px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors">
                                    + Tetapkan Anggaran
                                </a>
                            </div>
                        @endif
                    </div>
                    @if(($dashboard['budget']['has_budget'] ?? false))
                        <div class="pt-3 border-t border-gray-100 mt-3 text-right">
                            <a href="{{ route('budget.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                                Lihat Rincian Pos Anggaran →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                
                <!-- Cash Flow Chart (Bar) -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-bold text-gray-800">Arus Kas (Cash Flow)</h3>
                                <!-- Mode Switcher Toggle -->
                                <div class="inline-flex bg-gray-100 p-0.5 rounded-lg text-xs font-semibold">
                                    <button type="button" id="btnFlow7Days" onclick="switchCashFlowMode('7days')" class="px-2.5 py-1 rounded-md bg-white text-gray-800 shadow-sm transition-all">7 Hari</button>
                                    <button type="button" id="btnFlow12Months" onclick="switchCashFlowMode('12months')" class="px-2.5 py-1 rounded-md text-gray-500 hover:text-gray-800 transition-all">12 Bulan</button>
                                </div>
                            </div>
                            <p id="flowSubtitle" class="text-xs sm:text-sm text-gray-500 mt-0.5">7 Hari Terakhir</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 bg-green-500 rounded"></div>
                                <span class="text-xs font-medium text-gray-600">Pemasukan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 bg-red-500 rounded"></div>
                                <span class="text-xs font-medium text-gray-600">Pengeluaran</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-60 sm:h-72 relative w-full overflow-hidden">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>

                <!-- Expense Categories Chart (Donut) -->
                <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="mb-3">
                            <h3 class="text-base sm:text-lg font-bold text-gray-800">Alokasi Pengeluaran</h3>
                            <p class="text-xs text-gray-500">Persentase ke mana uang pergi bulan ini</p>
                        </div>
                        <div class="h-48 sm:h-52 flex items-center justify-center relative w-full overflow-hidden">
                            <canvas id="expenseDonutChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Breakdown Kategori Lengkap -->
                    <div class="mt-3 pt-3 border-t border-gray-100 max-h-40 overflow-y-auto space-y-1.5 text-xs">
                        @forelse(($dashboard['spending_by_category'] ?? []) as $cat)
                            <div class="flex items-center justify-between text-gray-700 py-0.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $cat['color'] ?? '#9ca3af' }}"></span>
                                    <span class="truncate font-medium">{{ $cat['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-right flex-shrink-0">
                                    <span class="text-gray-400 font-mono text-[11px]">{{ $cat['pct'] }}%</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-2">Belum ada pengeluaran bulan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Balance Trend Chart (Line) -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100 mb-6">
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Trend Saldo</h3>
                        <p class="text-xs sm:text-sm text-gray-500">30 Hari Terakhir</p>
                    </div>
                    <span class="text-xs bg-blue-50 text-blue-700 px-2.5 sm:px-3 py-1.5 rounded-lg font-semibold">
                        @if(count($balanceTrend) >= 2)
                            @php
                                $trend = end($balanceTrend) - reset($balanceTrend);
                                $isPositive = $trend >= 0;
                            @endphp
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                                @if($isPositive)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                @endif
                            </svg>
                            <span class="{{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isPositive ? '+' : '' }}{{ number_format(abs($trend), 0, ',', '.') }}
                            </span>
                        @endif
                    </span>
                </div>
                <div class="h-64 sm:h-80 relative w-full overflow-hidden">
                    <canvas id="balanceTrendChart"></canvas>
                </div>
            </div>

            <!-- Top 5 Pengeluaran Terbesar Bulan Ini -->
            @if(!empty($dashboard['top_expenses']))
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-800">Top Pengeluaran Terbesar</h3>
                            <p class="text-xs text-gray-500">Transaksi bernominal tertinggi di {{ $dashboard['period']['label'] ?? 'bulan ini' }}</p>
                        </div>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-2.5 py-1 rounded-lg">Fokus Efisiensi</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100">
                                <tr>
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">Keterangan</th>
                                    <th class="py-2.5 px-3">Kategori</th>
                                    <th class="py-2.5 px-3">Rekening</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($dashboard['top_expenses'] as $top)
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="py-2.5 px-3 text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($top['date'])->translatedFormat('d M Y') }}</td>
                                        <td class="py-2.5 px-3 font-bold text-gray-800">{{ $top['description'] }}</td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">
                                                {{ $top['category'] ?? 'Tanpa Kategori' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-gray-500">{{ $top['account'] ?? '-' }}</td>
                                        <td class="py-2.5 px-3 text-right font-extrabold text-red-600 whitespace-nowrap">
                                            -Rp {{ number_format($top['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Recent Transactions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="flex justify-between items-center p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800">Transaksi Terakhir</h3>
                    <a href="{{ route('transaction.index') }}" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                        Lihat Semua →
                    </a>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @forelse($recentTransactions as $transaction)
                        @php
                            $isCashIn = $transaction->type == 'Cash In';
                        @endphp
                        <div class="p-3.5 sm:p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 {{ $isCashIn ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6 {{ $isCashIn ? 'text-green-600' : 'text-red-600' }}">
                                            @if($isCashIn)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                            @endif
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 text-sm sm:text-base truncate">{{ $transaction->description }}</h4>
                                        <p class="text-[11px] sm:text-xs text-gray-500 truncate">
                                            {{ $transaction->type }} • {{ $transaction->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="font-bold text-sm sm:text-base md:text-lg whitespace-nowrap {{ $isCashIn ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $isCashIn ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-3 opacity-50">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <p class="font-medium">Belum ada transaksi</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // On load js
        document.addEventListener("DOMContentLoaded", function() {
             // function showing eye toggle
            document.querySelectorAll('.showing-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const balanceAmount = this.previousElementSibling;
                    if (balanceAmount.textContent.includes('••••••••')) {
                            // Show the balance
                        balanceAmount.textContent = 'Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}';
                    } else {
                        // Hide the balance
                        balanceAmount.textContent = 'Rp ••••••••';
                    }
                });
            });
        });

        // Helper function
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // 1. Cash Flow Bar Chart (Support 7 Hari & 12 Bulan Mode)
        const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
        const labels7Days = @json($last7Days);
        const cashIn7Days = @json($cashInData);
        const cashOut7Days = @json($cashOutData);

        const labels12Months = @json($dashboard['charts']['monthly_12']['labels'] ?? []);
        const cashIn12Months = @json($dashboard['charts']['monthly_12']['income'] ?? []);
        const cashOut12Months = @json($dashboard['charts']['monthly_12']['expense'] ?? []);

        const cashFlowChart = new Chart(cashFlowCtx, {
            type: 'bar',
            data: {
                labels: labels7Days,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: cashIn7Days,
                        backgroundColor: 'rgba(34, 197, 94, 0.85)',
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Pengeluaran',
                        data: cashOut7Days,
                        backgroundColor: 'rgba(239, 68, 68, 0.85)',
                        borderRadius: 8,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        borderRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                return 'Rp ' + (value / 1000) + 'k';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });

        function switchCashFlowMode(mode) {
            const btn7 = document.getElementById('btnFlow7Days');
            const btn12 = document.getElementById('btnFlow12Months');
            const subtitle = document.getElementById('flowSubtitle');

            if (mode === '7days') {
                cashFlowChart.data.labels = labels7Days;
                cashFlowChart.data.datasets[0].data = cashIn7Days;
                cashFlowChart.data.datasets[1].data = cashOut7Days;
                if (subtitle) subtitle.textContent = '7 Hari Terakhir';

                btn7.className = 'px-2.5 py-1 rounded-md bg-white text-gray-800 shadow-sm transition-all';
                btn12.className = 'px-2.5 py-1 rounded-md text-gray-500 hover:text-gray-800 transition-all';
            } else {
                cashFlowChart.data.labels = labels12Months;
                cashFlowChart.data.datasets[0].data = cashIn12Months;
                cashFlowChart.data.datasets[1].data = cashOut12Months;
                if (subtitle) subtitle.textContent = '12 Bulan Terakhir';

                btn12.className = 'px-2.5 py-1 rounded-md bg-white text-gray-800 shadow-sm transition-all';
                btn7.className = 'px-2.5 py-1 rounded-md text-gray-500 hover:text-gray-800 transition-all';
            }
            cashFlowChart.update();
        }

        // 2. Expense Donut Chart
        const expenseCtx = document.getElementById('expenseDonutChart').getContext('2d');
        const expenseLabels = @json($expenseLabels);
        const expenseData = @json($expenseData);

        const categoryPalette = [
            'rgba(239, 68, 68, 0.85)',
            'rgba(249, 115, 22, 0.85)',
            'rgba(234, 179, 8, 0.85)',
            'rgba(16, 185, 129, 0.85)',
            'rgba(59, 130, 246, 0.85)',
            'rgba(139, 92, 246, 0.85)',
            'rgba(236, 72, 153, 0.85)',
            'rgba(100, 116, 139, 0.85)'
        ];

        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: expenseLabels.length > 0 ? expenseLabels : ['Belum ada data'],
                datasets: [{
                    data: expenseData.length > 0 ? expenseData : [1],
                    backgroundColor: expenseLabels.length > 0 ? categoryPalette.slice(0, expenseLabels.length) : ['#e2e8f0'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false // Ditampilkan via list HTML di bawah canvas agar rapi
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        callbacks: {
                            label: function(context) {
                                if (expenseData.length === 0) return 'Belum ada data';
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + formatRupiah(context.raw) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 3. Balance Trend Line Chart
        const balanceCtx = document.getElementById('balanceTrendChart').getContext('2d');
        new Chart(balanceCtx, {
            type: 'line',
            data: {
                labels: @json($last30Days),
                datasets: [{
                    label: 'Saldo',
                    data: @json($balanceTrend),
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Saldo: ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { size: 10 },
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });
    </script>

    <!-- ========================================================================= -->
    <!-- MODAL 1: Upload PDF Statement                                             -->
    <!-- ========================================================================= -->
    <div id="pdfImportModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div onclick="closePdfImportModal()" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl sm:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white relative">
                    <button onclick="closePdfImportModal()" type="button" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold tracking-tight text-white">Import Transaksi Bank</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Ekstrak mutasi PDF dari Bank Jago, BCA, Mandiri, dll.</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <form id="pdfUploadForm" onsubmit="handlePdfExtraction(event)" class="p-6 space-y-5">
                    <!-- Pilih Rekening Bank -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Pilih Rekening Tujuan <span class="text-red-500">*</span>
                        </label>
                        @if($savings->isEmpty())
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-3.5 rounded-xl text-xs flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0 text-amber-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span>Anda belum memiliki rekening tabungan. <a href="{{ route('saving.index') }}" class="underline font-bold text-amber-900">Buat rekening</a> terlebih dahulu.</span>
                            </div>
                        @else
                            <div class="relative">
                                <select id="importPdfSavingsId" name="savings_id" required class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option value="" disabled selected>-- Pilih Rekening / Bank Anda --</option>
                                    @foreach($savings as $savingAccount)
                                        <option value="{{ $savingAccount->id }}" data-balance="{{ $savingAccount->current_balance }}" data-name="{{ $savingAccount->name }}">
                                            {{ $savingAccount->bank->name ?? 'Bank' }} - {{ $savingAccount->name }} (Saldo: Rp {{ number_format($savingAccount->current_balance, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Upload File PDF Area -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            File Dokumen PDF <span class="text-red-500">*</span>
                        </label>
                        <div id="pdfDropzone" class="border-2 border-dashed border-gray-300 hover:border-blue-500 bg-gray-50/70 hover:bg-blue-50/50 rounded-2xl p-6 text-center transition-all cursor-pointer relative group">
                            <input type="file" id="pdfFileInput" name="file" accept=".pdf,application/pdf" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelect(this)">
                            
                            <!-- State: Kosong / Belum Pilih -->
                            <div id="dropzoneEmpty">
                                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Pilih file PDF atau seret ke sini</p>
                                <p class="text-xs text-gray-400 mt-1">Format .pdf mutasi rekening e-statement (Maks. 20MB)</p>
                            </div>

                            <!-- State: File Terpilih -->
                            <div id="dropzoneSelected" class="hidden">
                                <div class="flex items-center justify-center gap-3 text-left">
                                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p id="selectedFileName" class="text-sm font-bold text-gray-800 truncate">document.pdf</p>
                                        <p id="selectedFileSize" class="text-xs text-gray-500">0 KB</p>
                                    </div>
                                    <button type="button" onclick="clearSelectedFile(event)" class="text-xs text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                                        Ganti
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password PDF (Opsional) -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Password PDF <span class="text-gray-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <span class="text-[11px] text-gray-500">Kosongkan jika file tidak terkunci</span>
                        </div>
                        <div class="relative">
                            <input type="password" id="importPdfPassword" name="password" placeholder="Contoh: Tanggal lahir DDMMYYYY (jika diproteksi)"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none pr-10">
                            <button type="button" onclick="togglePasswordVisibility('importPdfPassword')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 flex items-start gap-3">
                        <div class="text-blue-600 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                        <p class="text-xs text-blue-900 leading-relaxed">
                            Data akan diekstrak menggunakan parser AI & OCR. Anda dapat <strong>memeriksa dan mengubah</strong> seluruh nominal, tanggal, kategori, atau menghapus baris sebelum disimpan ke sistem.
                        </p>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closePdfImportModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all">
                            Batal
                        </button>
                        <button type="submit" id="btnStartExtract" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-2.5 px-5 rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <span>Mulai Ekstraksi</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: Loading Extraction State                                         -->
    <!-- ========================================================================= -->
    <div id="pdfImportLoadingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-md transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-gray-100 text-center overflow-hidden">
                <!-- Laser Scanning Animation Graphic -->
                <div class="relative w-24 h-28 mx-auto mb-6 bg-blue-50 border-2 border-blue-200 rounded-2xl flex flex-col justify-between p-3.5 shadow-md overflow-hidden">
                    <div class="space-y-1.5">
                        <div class="w-10 h-2 bg-blue-300 rounded"></div>
                        <div class="w-16 h-1.5 bg-blue-200 rounded"></div>
                        <div class="w-12 h-1.5 bg-blue-200 rounded"></div>
                    </div>
                    <div class="space-y-1.5">
                        <div class="w-14 h-1.5 bg-blue-200 rounded"></div>
                        <div class="w-8 h-1.5 bg-blue-200 rounded"></div>
                    </div>
                    <!-- Moving Scanner Bar -->
                    <div class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent shadow-[0_0_12px_rgba(59,130,246,0.9)] animate-scanner"></div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-1.5">Mengekstrak Transaksi PDF...</h3>
                <p class="text-xs text-gray-500 max-w-xs mx-auto mb-5 leading-relaxed">
                    Parser Python sedang membaca halaman e-statement dan mendeteksi kolom tanggal, deskripsi, serta nominal.
                </p>

                <!-- Animated Progress Indicator -->
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden mb-3">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full w-2/3 animate-pulse"></div>
                </div>

                <p class="text-[11px] text-gray-400 font-medium">Harap jangan menutup browser saat proses ekstraksi berlangsung.</p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: Data Review & Modification (UX High Priority)                    -->
    <!-- ========================================================================= -->
    <div id="pdfImportReviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-2 sm:p-4 text-center">
            <div onclick="closeReviewModal()" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"></div>
            
            <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-100 max-w-6xl w-full mx-auto my-4 text-left overflow-hidden flex flex-col max-h-[92vh]">
                
                <!-- Review Modal Header -->
                <div class="p-4 sm:p-6 bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 text-white flex-shrink-0">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                    Hasil Ekstraksi PDF
                                </span>
                                <span id="reviewTargetSavingBadge" class="bg-white/10 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full">
                                    Rekening
                                </span>
                                <span id="reviewParserBadge" class="hidden text-[11px] font-semibold px-2.5 py-0.5 rounded-full"></span>
                            </div>
                            <h2 class="text-lg sm:text-2xl font-black tracking-tight">Verifikasi & Sesuaikan Transaksi</h2>
                            <p class="text-xs text-blue-200 mt-0.5">Periksa, ubah nominal/kategori, atau hapus baris sebelum menyimpan ke catatan finansial.</p>
                        </div>

                        <!-- Stat Badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="bg-white/10 border border-white/15 px-3 py-1.5 rounded-xl">
                                <p class="text-[10px] text-blue-200 uppercase font-semibold">Total Item</p>
                                <p id="reviewTotalItemsBadge" class="text-sm font-extrabold text-white">0</p>
                            </div>
                            <div class="bg-green-500/15 border border-green-400/30 px-3 py-1.5 rounded-xl">
                                <p class="text-[10px] text-green-300 uppercase font-semibold">Total Masuk</p>
                                <p id="reviewTotalCashInBadge" class="text-sm font-extrabold text-green-400">+Rp 0</p>
                            </div>
                            <div class="bg-red-500/15 border border-red-400/30 px-3 py-1.5 rounded-xl">
                                <p class="text-[10px] text-red-300 uppercase font-semibold">Total Keluar</p>
                                <p id="reviewTotalCashOutBadge" class="text-sm font-extrabold text-red-400">-Rp 0</p>
                            </div>
                            <div id="reviewDuplicateStatBox" class="hidden bg-amber-500/20 border border-amber-400/40 px-3 py-1.5 rounded-xl">
                                <p class="text-[10px] text-amber-300 uppercase font-semibold">Duplikat</p>
                                <p id="reviewTotalDuplicatesBadge" class="text-sm font-extrabold text-amber-300">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Duplicate Alert Banner -->
                <div id="pdfDuplicateAlertBanner" class="hidden mx-4 sm:mx-6 mt-3 p-3.5 bg-amber-50/90 border border-amber-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900 shadow-sm flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">⚠️</span>
                        <div>
                            <p class="font-bold text-amber-950"><span id="duplicateAlertCount">0</span> transaksi terdeteksi serupa pada rekening ini.</p>
                            <p class="text-[11px] text-amber-800">Berdasarkan nama transaksi dan nominal yang cocok dengan catatan sebelumnya. Transaksi duplikat otomatis di-uncheck agar tidak tercatat ganda.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" onclick="selectOnlyNewTransactions()" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                            Pilih Hanya Transaksi Baru
                        </button>
                        <button type="button" onclick="selectAllTransactions()" class="px-3 py-1.5 bg-white hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs transition-colors">
                            Pilih Semua
                        </button>
                    </div>
                </div>

                <!-- Review Toolbar (Search, Filter, Select All, Balance Update Toggle) -->
                <div class="bg-gray-50 border-b border-gray-200 p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 flex-shrink-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Select All Checkbox -->
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span>Pilih Semua</span>
                        </label>

                        <!-- Filter Type Pills -->
                        <div class="inline-flex rounded-xl bg-gray-200/70 p-0.5 text-xs font-semibold">
                            <button type="button" onclick="setReviewFilter('all')" id="filterTabAll" class="filter-tab px-3 py-1 rounded-lg bg-white text-gray-800 shadow-sm transition-all">Semua</button>
                            <button type="button" onclick="setReviewFilter('Cash In')" id="filterTabIn" class="filter-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-800 transition-all">Pemasukan</button>
                            <button type="button" onclick="setReviewFilter('Cash Out')" id="filterTabOut" class="filter-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-800 transition-all">Pengeluaran</button>
                            <button type="button" onclick="setReviewFilter('duplicate')" id="filterTabDuplicate" class="filter-tab hidden px-3 py-1 rounded-lg text-amber-800 hover:text-amber-950 transition-all">⚠️ Duplikat (<span id="filterTabDuplicateCount">0</span>)</button>
                        </div>

                        <!-- Search Box -->
                        <div class="relative flex-1 sm:w-64">
                            <input type="text" id="reviewSearchInput" oninput="handleReviewSearch(this.value)" placeholder="Cari deskripsi atau nominal..."
                                   class="w-full bg-white border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Update Saldo Toggle -->
                    <div id="updateBalanceContainer" class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-gray-700 bg-blue-50/80 border border-blue-200/70 px-3 py-1.5 rounded-xl hover:bg-blue-100/60 transition-colors">
                            <input type="checkbox" id="updateBalanceCheckbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span>Perbarui saldo rekening ke saldo akhir PDF (<strong id="reviewFinalBalanceLabel" class="text-blue-700">Rp 0</strong>)</span>
                        </label>
                    </div>
                </div>

                <!-- Review Editable Table Container -->
                <div class="overflow-x-auto overflow-y-auto flex-1 p-2 sm:p-4 min-h-[300px]">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50/50 sticky top-0 z-10">
                                <th class="p-2.5 w-10 text-center">Pilih</th>
                                <th class="p-2.5 w-32">Tanggal</th>
                                <th class="p-2.5 w-32">Tipe</th>
                                <th class="p-2.5 min-w-[220px]">Deskripsi Transaksi</th>
                                <th class="p-2.5 w-44">Kategori</th>
                                <th class="p-2.5 w-44">Pos Anggaran</th>
                                <th class="p-2.5 w-36 text-right">Nominal (Rp)</th>
                                <th class="p-2.5 w-28 text-right">Saldo PDF</th>
                                <th class="p-2.5 w-12 text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="reviewTableBody" class="divide-y divide-gray-100 text-xs">
                            <!-- Rows dynamically populated by JavaScript -->
                        </tbody>
                    </table>

                    <!-- Empty State in Table -->
                    <div id="reviewEmptyState" class="hidden text-center py-12">
                        <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-2xl mx-auto flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Tidak ada transaksi yang cocok dengan filter</p>
                    </div>
                </div>

                <!-- Review Modal Footer -->
                <div class="bg-gray-50 border-t border-gray-200 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
                    <div class="text-xs text-gray-600">
                        Menampilkan <strong id="reviewCountVisible" class="text-gray-900">0</strong> transaksi | 
                        <strong id="reviewCountSelected" class="text-blue-700">0</strong> transaksi akan disimpan
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <button type="button" onclick="closeReviewModal()" class="flex-1 sm:flex-none px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                            Batal
                        </button>
                        <button type="button" id="btnSaveImported" onclick="submitImportedTransactions()" class="flex-1 sm:flex-none px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span id="btnSaveImportedText">Simpan Transaksi</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: Success Feedback Animation Modal                                 -->
    <!-- ========================================================================= -->
    <div id="pdfImportSuccessModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-md transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-gray-100 text-center overflow-hidden">
                <!-- Checkmark Success Animation -->
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full mx-auto flex items-center justify-center mb-5 animate-bounce shadow-lg shadow-green-100">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-gray-900 mb-1">Import Berhasil!</h3>
                <p id="pdfImportSuccessMessage" class="text-xs text-gray-600 mb-4 leading-relaxed">
                    Transaksi telah berhasil dicatat ke dalam database.
                </p>

                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full text-xs font-medium">
                    <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memperbarui data dashboard...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline Animation Styles for Scanner -->
    <style>
        @keyframes scanner {
            0% { top: 0%; opacity: 0.8; }
            50% { top: 90%; opacity: 1; }
            100% { top: 0%; opacity: 0.8; }
        }
        .animate-scanner {
            animation: scanner 2.2s ease-in-out infinite;
        }
    </style>

    <!-- ========================================================================= -->
    <!-- JAVASCRIPT LOGIC: PDF Import Flow & Editable Review Modal                  -->
    <!-- ========================================================================= -->
    <script>
        // Master Data dari Backend
        window.availableCategories = @json($categories ?? []);
        window.availableBudgetItems = @json($budgetItems ?? []);
        window.currentPdfTransactions = [];
        window.currentPdfSummary = {};
        window.selectedSavingId = null;
        window.selectedSavingName = '';
        window.currentReviewFilter = 'all';
        window.currentReviewSearch = '';

        // Modal Controls
        function openPdfImportModal() {
            document.getElementById('pdfImportModal').classList.remove('hidden');
        }

        function closePdfImportModal() {
            document.getElementById('pdfImportModal').classList.add('hidden');
        }

        function closeReviewModal() {
            if (confirm('Batalkan import data transaksi ini? Data yang belum disimpan akan hilang.')) {
                document.getElementById('pdfImportReviewModal').classList.add('hidden');
            }
        }

        function handleFileSelect(input) {
            const emptyEl = document.getElementById('dropzoneEmpty');
            const selectedEl = document.getElementById('dropzoneSelected');
            const nameEl = document.getElementById('selectedFileName');
            const sizeEl = document.getElementById('selectedFileSize');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                nameEl.textContent = file.name;
                sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
                emptyEl.classList.add('hidden');
                selectedEl.classList.remove('hidden');
            }
        }

        function clearSelectedFile(event) {
            event.stopPropagation();
            event.preventDefault();
            const input = document.getElementById('pdfFileInput');
            input.value = '';
            document.getElementById('dropzoneEmpty').classList.remove('hidden');
            document.getElementById('dropzoneSelected').classList.add('hidden');
        }

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Handle Ekstraksi PDF (AJAX Call to Controller)
        async function handlePdfExtraction(event) {
            event.preventDefault();

            const savingsSelect = document.getElementById('importPdfSavingsId');
            const savingsId = savingsSelect.value;
            const savingOption = savingsSelect.options[savingsSelect.selectedIndex];
            window.selectedSavingId = savingsId;
            window.selectedSavingName = savingOption ? savingOption.getAttribute('data-name') : 'Rekening';

            const fileInput = document.getElementById('pdfFileInput');
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Silakan pilih file PDF terlebih dahulu.');
                return;
            }

            const password = document.getElementById('importPdfPassword').value;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('savings_id', savingsId);
            if (password) {
                formData.append('password', password);
            }

            // Tutup upload modal & buka loading modal
            closePdfImportModal();
            document.getElementById('pdfImportLoadingModal').classList.remove('hidden');

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch("{{ route('transaction.import_pdf') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();
                document.getElementById('pdfImportLoadingModal').classList.add('hidden');

                if (!response.ok || result.status !== 'success') {
                    alert(result.message || 'Gagal mengekstrak PDF. Silakan periksa format file atau password.');
                    openPdfImportModal();
                    return;
                }

                // Simpan transaksi dan buka review modal
                window.currentPdfSummary = result.data;
                window.currentPdfTransactions = (result.data.transactions || []).map((t, idx) => ({
                    ...t,
                    unique_id: idx,
                    is_selected: t.is_selected !== undefined ? Boolean(t.is_selected) : !Boolean(t.is_duplicate),
                }));

                populateReviewModal();
                document.getElementById('pdfImportReviewModal').classList.remove('hidden');

            } catch (err) {
                document.getElementById('pdfImportLoadingModal').classList.add('hidden');
                alert('Terjadi kesalahan jaringan atau server saat mengekstrak PDF: ' + err.message);
                openPdfImportModal();
            }
        }

        // Populate Review Modal & Render Rows
        function populateReviewModal() {
            document.getElementById('reviewTargetSavingBadge').textContent = window.selectedSavingName;
            
            const parserBadge = document.getElementById('reviewParserBadge');
            if (parserBadge && window.currentPdfSummary) {
                parserBadge.classList.remove('hidden');
                if (window.currentPdfSummary.is_ai_parsed) {
                    parserBadge.className = 'bg-purple-500/25 text-purple-200 border border-purple-400/40 text-[11px] font-semibold px-2.5 py-0.5 rounded-full';
                    parserBadge.textContent = '✨ AI Extractor';
                } else if (window.currentPdfSummary.parser_name) {
                    parserBadge.className = 'bg-emerald-500/25 text-emerald-200 border border-emerald-400/40 text-[11px] font-semibold px-2.5 py-0.5 rounded-full';
                    parserBadge.textContent = '⚡ ' + window.currentPdfSummary.parser_name;
                }
            }

            const finalBal = window.currentPdfSummary.final_balance;
            const finalBalFmt = window.currentPdfSummary.final_balance_fmt || 'Rp 0';
            
            const balContainer = document.getElementById('updateBalanceContainer');
            if (finalBal !== null && finalBal !== undefined) {
                balContainer.classList.remove('hidden');
                document.getElementById('reviewFinalBalanceLabel').textContent = finalBalFmt;
            } else {
                balContainer.classList.add('hidden');
            }

            // Update badge dan alert duplikat
            const dupCount = Number(window.currentPdfSummary.duplicate_count || 0);
            const dupStatBox = document.getElementById('reviewDuplicateStatBox');
            const dupAlertBanner = document.getElementById('pdfDuplicateAlertBanner');
            const dupFilterTab = document.getElementById('filterTabDuplicate');

            if (dupCount > 0) {
                if (dupStatBox) {
                    dupStatBox.classList.remove('hidden');
                    document.getElementById('reviewTotalDuplicatesBadge').textContent = dupCount;
                }
                if (dupAlertBanner) {
                    dupAlertBanner.classList.remove('hidden');
                    document.getElementById('duplicateAlertCount').textContent = dupCount;
                }
                if (dupFilterTab) {
                    dupFilterTab.classList.remove('hidden');
                    document.getElementById('filterTabDuplicateCount').textContent = dupCount;
                }
            } else {
                if (dupStatBox) dupStatBox.classList.add('hidden');
                if (dupAlertBanner) dupAlertBanner.classList.add('hidden');
                if (dupFilterTab) dupFilterTab.classList.add('hidden');
            }

            renderReviewTable();
        }

        function setReviewFilter(type) {
            window.currentReviewFilter = type;
            
            // Update Tab styles
            ['filterTabAll', 'filterTabIn', 'filterTabOut', 'filterTabDuplicate'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.remove('bg-white', 'text-gray-800', 'shadow-sm', 'bg-amber-100', 'text-amber-950');
                    el.classList.add('text-gray-600');
                }
            });

            if (type === 'all') {
                const el = document.getElementById('filterTabAll');
                el.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
                el.classList.remove('text-gray-600');
            } else if (type === 'Cash In') {
                const el = document.getElementById('filterTabIn');
                el.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
                el.classList.remove('text-gray-600');
            } else if (type === 'Cash Out') {
                const el = document.getElementById('filterTabOut');
                el.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
                el.classList.remove('text-gray-600');
            } else if (type === 'duplicate') {
                const el = document.getElementById('filterTabDuplicate');
                if (el) {
                    el.classList.add('bg-amber-100', 'text-amber-950', 'shadow-sm');
                    el.classList.remove('text-gray-600');
                }
            }

            renderReviewTable();
        }

        function handleReviewSearch(query) {
            window.currentReviewSearch = (query || '').toLowerCase().trim();
            renderReviewTable();
        }

        function toggleSelectAll(masterCheckbox) {
            const isChecked = masterCheckbox.checked;
            const visibleRows = getFilteredTransactions();
            visibleRows.forEach(row => {
                const original = window.currentPdfTransactions.find(t => t.unique_id === row.unique_id);
                if (original) original.is_selected = isChecked;
            });
            renderReviewTable();
        }

        function selectOnlyNewTransactions() {
            window.currentPdfTransactions.forEach(t => {
                t.is_selected = !t.is_duplicate;
            });
            const masterCb = document.getElementById('selectAllCheckbox');
            if (masterCb) masterCb.checked = false;
            renderReviewTable();
        }

        function selectAllTransactions() {
            window.currentPdfTransactions.forEach(t => {
                t.is_selected = true;
            });
            const masterCb = document.getElementById('selectAllCheckbox');
            if (masterCb) masterCb.checked = true;
            renderReviewTable();
        }

        function getFilteredTransactions() {
            return window.currentPdfTransactions.filter(item => {
                // Filter Type
                if (window.currentReviewFilter === 'duplicate') {
                    if (!item.is_duplicate) return false;
                } else if (window.currentReviewFilter !== 'all' && item.type !== window.currentReviewFilter) {
                    return false;
                }
                // Filter Search
                if (window.currentReviewSearch) {
                    const descMatch = (item.description || '').toLowerCase().includes(window.currentReviewSearch);
                    const amountMatch = String(item.amount || '').includes(window.currentReviewSearch);
                    const catMatch = (item.category_name || '').toLowerCase().includes(window.currentReviewSearch);
                    if (!descMatch && !amountMatch && !catMatch) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Render Table Body
        function renderReviewTable() {
            const tbody = document.getElementById('reviewTableBody');
            const emptyState = document.getElementById('reviewEmptyState');
            const filtered = getFilteredTransactions();

            // Hitung statistik
            let totalIn = 0;
            let totalOut = 0;
            let selectedCount = 0;

            window.currentPdfTransactions.forEach(t => {
                if (t.is_selected) {
                    selectedCount++;
                    if (t.type === 'Cash In') totalIn += Number(t.amount || 0);
                    else totalOut += Number(t.amount || 0);
                }
            });

            document.getElementById('reviewTotalItemsBadge').textContent = window.currentPdfTransactions.length;
            document.getElementById('reviewTotalCashInBadge').textContent = '+Rp ' + Number(totalIn).toLocaleString('id-ID');
            document.getElementById('reviewTotalCashOutBadge').textContent = '-Rp ' + Number(totalOut).toLocaleString('id-ID');
            document.getElementById('reviewCountVisible').textContent = filtered.length;
            document.getElementById('reviewCountSelected').textContent = selectedCount;

            const btnSave = document.getElementById('btnSaveImported');
            if (selectedCount === 0) {
                btnSave.disabled = true;
                btnSave.classList.add('opacity-50', 'cursor-not-allowed');
                document.getElementById('btnSaveImportedText').textContent = 'Pilih Transaksi';
            } else {
                btnSave.disabled = false;
                btnSave.classList.remove('opacity-50', 'cursor-not-allowed');
                document.getElementById('btnSaveImportedText').textContent = `Simpan ${selectedCount} Transaksi`;
            }

            if (filtered.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            tbody.innerHTML = filtered.map(row => {
                const categoryOptions = window.availableCategories.map(cat => 
                    `<option value="${cat.id}" ${cat.id == row.category_id ? 'selected' : ''}>${cat.name}</option>`
                ).join('');

                const budgetItemOptions = window.availableBudgetItems.map(item => 
                    `<option value="${item.id}" ${item.id == row.budget_item_id ? 'selected' : ''}>${item.name}</option>`
                ).join('');

                const typeBadgeColor = row.type === 'Cash In' ? 'text-green-600 bg-green-50 border-green-200' : 'text-red-600 bg-red-50 border-red-200';
                const isDup = Boolean(row.is_duplicate);
                const rowBgClass = isDup
                    ? (row.is_selected ? 'bg-amber-50/50 hover:bg-amber-100/40 border-l-4 border-l-amber-400' : 'bg-amber-50/20 opacity-50')
                    : (!row.is_selected ? 'opacity-40 bg-gray-50' : 'hover:bg-blue-50/40');

                const duplicateInfoHtml = isDup ? `
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" title="${escapeHtml(row.duplicate_reason || 'Terdeteksi serupa')}">
                            ⚠️ Duplikat
                        </span>
                        <span class="text-[10px] text-amber-800 truncate max-w-xs" title="${escapeHtml(row.duplicate_reason || '')}">
                            ${escapeHtml(row.duplicate_reason || 'Sudah tercatat di rekening ini')}
                        </span>
                    </div>
                ` : '';

                return `
                    <tr class="transition-colors ${rowBgClass}" data-id="${row.unique_id}">
                        <!-- Checkbox -->
                        <td class="p-2.5 text-center">
                            <input type="checkbox" onchange="updateRowField(${row.unique_id}, 'is_selected', this.checked)" ${row.is_selected ? 'checked' : ''}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                        </td>

                        <!-- Tanggal -->
                        <td class="p-2.5">
                            <input type="date" value="${row.transaction_date}" onchange="updateRowField(${row.unique_id}, 'transaction_date', this.value)"
                                   class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-800 font-medium focus:ring-1 focus:ring-blue-500 outline-none">
                        </td>

                        <!-- Tipe -->
                        <td class="p-2.5">
                            <select onchange="updateRowField(${row.unique_id}, 'type', this.value)"
                                    class="w-full border rounded-lg px-2 py-1 text-xs font-bold ${typeBadgeColor} focus:ring-1 focus:ring-blue-500 outline-none">
                                <option value="Cash Out" ${row.type === 'Cash Out' ? 'selected' : ''}>Cash Out (Keluar)</option>
                                <option value="Cash In" ${row.type === 'Cash In' ? 'selected' : ''}>Cash In (Masuk)</option>
                                <option value="Transfer" ${row.type === 'Transfer' ? 'selected' : ''}>Transfer</option>
                                <option value="Tabungan" ${row.type === 'Tabungan' ? 'selected' : ''}>Tabungan</option>
                            </select>
                        </td>

                        <!-- Deskripsi -->
                        <td class="p-2.5">
                            <input type="text" value="${escapeHtml(row.description)}" onchange="updateRowField(${row.unique_id}, 'description', this.value)"
                                   class="w-full bg-white border ${isDup ? 'border-amber-300' : 'border-gray-200'} rounded-lg px-2 py-1 text-xs text-gray-800 font-medium focus:ring-1 focus:ring-blue-500 outline-none">
                            ${duplicateInfoHtml}
                        </td>

                        <!-- Kategori -->
                        <td class="p-2.5">
                            <select onchange="updateRowField(${row.unique_id}, 'category_id', this.value)"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none">
                                <option value="">-- Tanpa Kategori --</option>
                                ${categoryOptions}
                            </select>
                        </td>

                        <!-- Pos Anggaran -->
                        <td class="p-2.5">
                            <select onchange="updateRowField(${row.unique_id}, 'budget_item_id', this.value)"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none">
                                <option value="">-- Tanpa Anggaran --</option>
                                ${budgetItemOptions}
                            </select>
                        </td>

                        <!-- Nominal -->
                        <td class="p-2.5 text-right">
                            <input type="number" step="any" min="0" value="${row.amount}" onchange="updateRowField(${row.unique_id}, 'amount', parseFloat(this.value) || 0)"
                                   class="w-full text-right bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs font-bold text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none">
                        </td>

                        <!-- Saldo PDF -->
                        <td class="p-2.5 text-right font-mono text-[11px] text-gray-500">
                            ${row.balance_formatted || '-'}
                        </td>

                        <!-- Delete Row Button -->
                        <td class="p-2.5 text-center">
                            <button type="button" onclick="deleteReviewRow(${row.unique_id})" title="Hapus baris ini dari daftar import"
                                    class="w-7 h-7 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg inline-flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function updateRowField(uniqueId, field, value) {
            const row = window.currentPdfTransactions.find(t => t.unique_id === uniqueId);
            if (!row) return;
            row[field] = value;
            renderReviewTable();
        }

        function deleteReviewRow(uniqueId) {
            window.currentPdfTransactions = window.currentPdfTransactions.filter(t => t.unique_id !== uniqueId);
            renderReviewTable();
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Simpan Hasil Ekstraksi ke Database
        async function submitImportedTransactions() {
            const selectedRows = window.currentPdfTransactions.filter(t => t.is_selected);
            if (selectedRows.length === 0) {
                alert('Pilih minimal satu transaksi untuk disimpan.');
                return;
            }

            const shouldUpdateBalance = document.getElementById('updateBalanceCheckbox').checked;
            const finalBalance = window.currentPdfSummary.final_balance;

            const btnSave = document.getElementById('btnSaveImported');
            const originalText = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
            `;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch("{{ route('transaction.import_pdf_save') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        savings_id: window.selectedSavingId,
                        update_balance: shouldUpdateBalance,
                        final_balance: finalBalance,
                        transactions: selectedRows.map(r => ({
                            transaction_date: r.transaction_date,
                            type: r.type,
                            description: r.description,
                            amount: r.amount,
                            category_id: r.category_id || null,
                            budget_item_id: r.budget_item_id || null,
                            is_selected: true,
                        })),
                    }),
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    alert(result.message || 'Gagal menyimpan transaksi ke database.');
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalText;
                    return;
                }

                // Berhasil! Tutup review modal, tampilkan success modal, lalu refresh dashboard
                document.getElementById('pdfImportReviewModal').classList.add('hidden');
                document.getElementById('pdfImportSuccessMessage').textContent = result.message || 'Transaksi berhasil diimport!';
                document.getElementById('pdfImportSuccessModal').classList.remove('hidden');

                // Tunggu animasi 1.5 detik lalu refresh halaman secara otomatis
                setTimeout(() => {
                    window.location.reload();
                }, 1600);

            } catch (err) {
                alert('Terjadi kesalahan saat menyimpan: ' + err.message);
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
            }
        }
    </script>
</x-app-layout>