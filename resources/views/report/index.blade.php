<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header & Export Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">📊 Laporan Keuangan</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $report['period_label'] }} ({{ $report['start_date'] }} s/d {{ $report['end_date'] }})</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('report.export_excel', ['mode' => $report['mode'], 'month' => $report['month'], 'year' => $report['year']]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm transition">
                    <span>📥 Export Excel (.xlsx)</span>
                </a>
                <a href="{{ route('report.print_pdf', ['mode' => $report['mode'], 'month' => $report['month'], 'year' => $report['year']]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#234066] hover:bg-[#1a304e] text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm transition">
                    <span>🖨️ Cetak / Simpan PDF</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <form action="{{ route('report.index') }}" method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mode Laporan</label>
                <select name="mode" onchange="this.form.submit()" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white">
                    <option value="monthly" {{ $report['mode'] === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $report['mode'] === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>

            @if($report['mode'] === 'monthly')
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bulan</label>
                    <select name="month" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white">
                        @foreach($months as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $report['month'] === $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun</label>
                <select name="year" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white">
                    @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $report['year'] === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition">
                Tampilkan
            </button>
        </form>

        <!-- Cash Flow & Net Worth Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-emerald-50/70 border border-emerald-100 rounded-2xl p-5">
                <p class="text-xs font-bold text-emerald-700 uppercase">Total Pemasukan</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">+ Rp {{ number_format($report['total_income'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-5">
                <p class="text-xs font-bold text-rose-700 uppercase">Total Pengeluaran</p>
                <p class="text-2xl font-extrabold text-rose-700 mt-1">- Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <p class="text-xs font-bold text-gray-500 uppercase">Net Cashflow (Savings Rate {{ $report['savings_rate'] }}%)</p>
                <p class="text-2xl font-extrabold mt-1 {{ $report['net_cashflow'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $report['net_cashflow'] >= 0 ? '+' : '-' }} Rp {{ number_format(abs($report['net_cashflow']), 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs font-bold text-blue-100 uppercase">Kekayaan Bersih (Net Worth)</p>
                <p class="text-2xl font-extrabold mt-1">Rp {{ number_format($report['net_worth_summary']['net_worth'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-blue-100 mt-1">
                    Kas + Aset (Rp {{ number_format($report['net_worth_summary']['total_assets'], 0, ',', '.') }}) - Hutang
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pengeluaran per Kategori -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-800">Pengeluaran per Kategori</h2>
                @if(!empty($report['category_breakdown']))
                    <div class="space-y-2.5">
                        @foreach($report['category_breakdown'] as $cat)
                            <div class="p-3 rounded-xl bg-gray-50/70 border border-gray-100">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800">
                                    <span>{{ $cat['category'] }} ({{ $cat['count'] }}x)</span>
                                    <span class="text-rose-600">Rp {{ number_format($cat['total'], 0, ',', '.') }} ({{ $cat['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $cat['percentage']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 py-4 text-center">Belum ada pengeluaran pada periode ini.</p>
                @endif
            </div>

            <!-- Realisasi Anggaran -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-800">Realisasi Anggaran vs Pengeluaran</h2>
                @if(!empty($report['budget_realization']))
                    <div class="space-y-2.5">
                        @foreach($report['budget_realization'] as $b)
                            <div class="p-3 rounded-xl bg-gray-50/70 border border-gray-100">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800">
                                    <span>{{ $b['name'] }}</span>
                                    <span class="{{ $b['status'] === 'overbudget' ? 'text-rose-600' : ($b['status'] === 'warning' ? 'text-amber-600' : 'text-emerald-600') }}">
                                        Rp {{ number_format($b['spent'], 0, ',', '.') }} / Rp {{ number_format($b['limit'], 0, ',', '.') }} ({{ $b['percentage'] }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $b['status'] === 'overbudget' ? 'bg-rose-500' : ($b['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $b['percentage']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 py-4 text-center">Tidak ada data anggaran pada periode ini.</p>
                @endif
            </div>
        </div>
    </main>
</x-app-layout>
