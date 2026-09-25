<x-app-layout>
    @php
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear  = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear  = $month == 12 ? $year + 1 : $year;

        $colorMap = [
            'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'badge' => 'bg-amber-100 text-amber-800'],
            'pink'    => ['bg' => 'bg-pink-50',    'text' => 'text-pink-700',    'border' => 'border-pink-200',    'badge' => 'bg-pink-100 text-pink-800'],
            'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'border' => 'border-blue-200',    'badge' => 'bg-blue-100 text-blue-800'],
            'yellow'  => ['bg' => 'bg-yellow-50',  'text' => 'text-yellow-700',  'border' => 'border-yellow-200',  'badge' => 'bg-yellow-100 text-yellow-800'],
            'purple'  => ['bg' => 'bg-purple-50',  'text' => 'text-purple-700',  'border' => 'border-purple-200',  'badge' => 'bg-purple-100 text-purple-800'],
            'red'     => ['bg' => 'bg-red-50',     'text' => 'text-red-700',     'border' => 'border-red-200',     'badge' => 'bg-red-100 text-red-800'],
            'gray'    => ['bg' => 'bg-gray-50',    'text' => 'text-gray-700',    'border' => 'border-gray-200',    'badge' => 'bg-gray-100 text-gray-800'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-800'],
            'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'border' => 'border-indigo-200',  'badge' => 'bg-indigo-100 text-indigo-800'],
            'teal'    => ['bg' => 'bg-teal-50',    'text' => 'text-teal-700',    'border' => 'border-teal-200',    'badge' => 'bg-teal-100 text-teal-800'],
            'orange'  => ['bg' => 'bg-orange-50',  'text' => 'text-orange-700',  'border' => 'border-orange-200',  'badge' => 'bg-orange-100 text-orange-800'],
        ];

        $overallStatus = 'safe';
        if ($totalSpent > $totalBudget && $totalBudget > 0) {
            $overallStatus = 'overbudget';
        } elseif ($overallPercentage >= 80) {
            $overallStatus = 'warning';
        }
    @endphp

    <div class="py-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <p class="text-emerald-800 font-semibold text-sm flex-1">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600 transition-colors p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-9 h-9 bg-rose-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1 text-rose-800 text-sm">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Header & Action -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#234066] flex items-center gap-2">
                        <span>🎯</span> Anggaran Seluruh Kebutuhan
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Alokasikan batas pengeluaran untuk seluruh kebutuhan bulanan Anda (Kos, Makan, Sedekah, Belanja, dll.) dan pantau realisasinya secara real-time
                    </p>
                </div>

                <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
                    @if(!empty($hasPreviousBudgets))
                        <form action="{{ route('budget.copy_previous') }}" method="POST" onsubmit="return confirm('Salin semua anggaran kebutuhan dari bulan {{ $indonesianMonths[$prevMonth] ?? $prevMonth }} {{ $prevYear }} ke bulan ini?');">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit" class="w-full md:w-auto justify-center inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-[#234066] text-sm font-semibold rounded-xl border border-blue-200 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                </svg>
                                Salin dari Bulan Lalu
                            </button>
                        </form>
                    @endif

                    <button onclick="openCreateModal()" class="w-full md:w-auto justify-center inline-flex items-center gap-2 px-5 py-2.5 bg-[#234066] hover:bg-[#1a304e] text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-900/10 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Atur Anggaran Kebutuhan
                    </button>
                </div>
            </div>

            <!-- Month / Period Selector Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                    <a href="{{ route('budget.index', ['month' => $prevMonth, 'year' => $prevYear]) }}" 
                       class="p-2 sm:px-3 sm:py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-gray-200 inline-flex items-center gap-1 text-sm font-medium"
                       title="Bulan Sebelumnya">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </a>

                    <div class="text-center px-3 py-1">
                        <span class="text-base sm:text-lg font-bold text-gray-800">
                            {{ $indonesianMonths[$month] ?? Carbon\Carbon::createFromDate($year, $month, 1)->format('F') }} {{ $year }}
                        </span>
                        @if($month == now()->month && $year == now()->year)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                Bulan Ini
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('budget.index', ['month' => $nextMonth, 'year' => $nextYear]) }}" 
                       class="p-2 sm:px-3 sm:py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-gray-200 inline-flex items-center gap-1 text-sm font-medium"
                       title="Bulan Berikutnya">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Jump to Month Form -->
                <form action="{{ route('budget.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="month" class="w-1/2 sm:w-auto text-xs sm:text-sm border-gray-200 rounded-xl py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                        @foreach($indonesianMonths as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $mNum == $month ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="w-1/2 sm:w-auto text-xs sm:text-sm border-gray-200 rounded-xl py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <button type="submit" class="p-2 text-xs sm:text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all">
                        Terapkan
                    </button>
                    
                    @if($month != now()->month || $year != now()->year)
                        <a href="{{ route('budget.index') }}" class="p-2 text-xs text-blue-600 hover:underline whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Summary Cards Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Anggaran -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Anggaran</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-wallet text-sm"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl font-extrabold text-gray-900">
                        Rp {{ number_format($totalBudget, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $budgets->count() }} kebutuhan dianggarkan
                    </p>
                </div>

                <!-- Total Terpakai -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Terpakai</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-receipt text-sm"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl font-extrabold text-gray-900">
                        Rp {{ number_format($totalSpent, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Pengeluaran riil bulan ini
                    </p>
                </div>

                <!-- Sisa Anggaran -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Sisa Anggaran</span>
                        <div class="w-8 h-8 rounded-xl {{ $totalRemaining >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                            <i class="fas {{ $totalRemaining >= 0 ? 'fa-piggy-bank' : 'fa-triangle-exclamation' }} text-sm"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl font-extrabold {{ $totalRemaining >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        Rp {{ number_format(abs($totalRemaining), 0, ',', '.') }}
                    </div>
                    <p class="text-xs {{ $totalRemaining >= 0 ? 'text-gray-500' : 'text-rose-500 font-semibold' }} mt-1">
                        {{ $totalRemaining >= 0 ? 'Tersedia untuk digunakan' : 'Melebihi total batas anggaran!' }}
                    </p>
                </div>

                <!-- Status Keseluruhan -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Status Pemakaian</span>
                        @if($overallStatus === 'safe')
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700">Aman</span>
                        @elseif($overallStatus === 'warning')
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">Waspada</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700">Overbudget</span>
                        @endif
                    </div>
                    <div class="text-xl sm:text-2xl font-extrabold text-gray-900">
                        {{ $overallPercentage }}%
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mt-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $overallStatus === 'safe' ? 'bg-emerald-500' : ($overallStatus === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"
                             style="width: {{ min(100, $overallPercentage) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Unbudgeted Category Suggestions Bar (if any available) -->
            @if($availableCategories->isNotEmpty())
                <div class="bg-white/80 backdrop-blur rounded-2xl p-4 border border-blue-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm font-semibold text-gray-800">
                                Kebutuhan Belum Dianggarkan ({{ $availableCategories->count() }})
                            </p>
                            <p class="text-[11px] sm:text-xs text-gray-500">
                                Klik untuk langsung menetapkan anggaran bulan ini
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap w-full md:w-auto">
                        @foreach($availableCategories->take(4) as $cat)
                            <button onclick="quickAddBudget({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg border border-gray-200 hover:border-blue-200 transition-all">
                                <i class="fas {{ $cat->icon ?? 'fa-tag' }} text-[10px]"></i>
                                <span>{{ $cat->name }}</span>
                                <span class="text-blue-600 font-bold ml-0.5">+</span>
                            </button>
                        @endforeach
                        @if($availableCategories->count() > 4)
                            <button onclick="openCreateModal()" class="text-xs text-blue-600 hover:underline px-2 py-1">
                                + {{ $availableCategories->count() - 4 }} lainnya
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Budgets List / Grid -->
            @if($budgets->isEmpty())
                <div class="bg-white rounded-3xl p-8 sm:p-12 text-center border border-gray-100 shadow-sm my-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-50 text-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bullseye text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1">Belum Ada Kebutuhan yang Dianggarkan</h3>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mb-6">
                        Buat anggaran untuk setiap kebutuhan pengeluaran Anda (Kos, Makan, Sedekah, Belanja, dll.) di {{ $indonesianMonths[$month] ?? '' }} {{ $year }} agar seluruh kebutuhan terpantau.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        @if(!empty($hasPreviousBudgets))
                            <form action="{{ route('budget.copy_previous') }}" method="POST" onsubmit="return confirm('Salin semua anggaran kebutuhan dari bulan {{ $indonesianMonths[$prevMonth] ?? $prevMonth }} {{ $prevYear }} ke bulan ini?');">
                                @csrf
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 bg-blue-50 hover:bg-blue-100 text-[#234066] font-semibold text-sm rounded-xl border border-blue-200 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                    </svg>
                                    Salin Anggaran Bulan Lalu
                                </button>
                            </form>
                        @endif

                        <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-[#234066] hover:bg-[#1a304e] text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-900/10 transition-all hover:scale-[1.02]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Mulai Atur Anggaran Kebutuhan
                        </button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($budgets as $budget)
                        @php
                            $c = $colorMap[$budget->category->color ?? 'gray'] ?? $colorMap['gray'];
                            $spent = $budget->spent;
                            $amount = (float) $budget->amount;
                            $pct = $budget->percentage;
                            $status = $budget->status; // 'safe', 'warning', 'overbudget'
                        @endphp

                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Top Bar: Icon, Name & Status Badge -->
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $c['bg'] }} {{ $c['text'] }} border {{ $c['border'] }}">
                                            <i class="fas {{ $budget->category->icon ?? 'fa-tag' }} text-base"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-base leading-tight">{{ $budget->name ?: $budget->category->name }}</h3>
                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md {{ $c['bg'] }} {{ $c['text'] }}">
                                                    <i class="fas {{ $budget->category->icon ?? 'fa-tag' }} text-[9px]"></i>
                                                    {{ $budget->category->name }}
                                                </span>
                                                <span class="text-[11px] text-gray-400">Batas: Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($status === 'safe')
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aman
                                        </span>
                                    @elseif($status === 'warning')
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                            Waspada
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                                            Overbudget
                                        </span>
                                    @endif
                                </div>

                                <!-- Progress Bar Section -->
                                <div class="space-y-1.5 mb-4">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-gray-600">Penggunaan</span>
                                        <span class="font-bold {{ $status === 'safe' ? 'text-gray-700' : ($status === 'warning' ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $pct }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-2.5 rounded-full transition-all duration-500 {{ $status === 'safe' ? 'bg-emerald-500' : ($status === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                </div>

                                <!-- Numbers Detail -->
                                <div class="grid grid-cols-2 gap-2 bg-gray-50/80 rounded-xl p-3 text-xs mb-4">
                                    <div>
                                        <span class="text-gray-400 block text-[11px]">Terpakai:</span>
                                        <span class="font-bold text-gray-800 text-sm">Rp {{ number_format($spent, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-400 block text-[11px]">{{ $spent > $amount ? 'Melebihi:' : 'Tersisa:' }}</span>
                                        <span class="font-bold text-sm {{ $spent > $amount ? 'text-rose-600' : 'text-emerald-600' }}">
                                            Rp {{ number_format(abs($amount - $spent), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                                <button onclick="openEditModal({{ $budget->id }}, '{{ addslashes($budget->name ?: $budget->category->name) }}', {{ $budget->category_id }}, {{ $amount }})"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    Ubah Pos
                                </button>

                                <button onclick="openDeleteModal({{ $budget->id }}, '{{ addslashes($budget->name ?: $budget->category->name) }}')"
                                        class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                        title="Hapus Anggaran">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <!-- Modal Tambah Anggaran -->
    <div id="modal-create" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/50 backdrop-blur-xs" onclick="closeCreateModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-bullseye text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Atur Anggaran Kebutuhan</h3>
                    </div>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('budget.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Periode</label>
                        <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700">
                            📅 {{ $indonesianMonths[$month] ?? '' }} {{ $year }}
                        </div>
                    </div>

                    <!-- Input Nama Kebutuhan / Pos Anggaran -->
                    <div>
                        <label for="create_name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Nama Kebutuhan / Pos Anggaran <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                                <i class="fas fa-tag text-xs"></i>
                            </span>
                            <input type="text" name="name" id="create_name" required maxlength="100" placeholder="Contoh: Kontrakan, Listrik, Tabungan Mamah, Wifi, Bensin"
                                   class="w-full pl-9 pr-4 py-3 text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Nama spesifik pos pengeluaran Anda.</p>
                    </div>

                    <!-- Dropdown Kategori Master Keuangan -->
                    <div>
                        <label for="create_category_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Kategori Keuangan <span class="text-rose-500">*</span>
                        </label>
                        <select name="category_id" id="create_category_id" required class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">-- Pilih Kategori Standar --</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Kelompokkan kebutuhan ke kategori master standar.</p>
                    </div>

                    <div>
                        <label for="create_amount" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Batas Anggaran (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-semibold text-sm">Rp</span>
                            <input type="number" name="amount" id="create_amount" required min="1000" step="500" placeholder="Contoh: 1500000"
                                   class="w-full pl-12 pr-4 py-3 text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeCreateModal()" class="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-[#234066] hover:bg-[#1a304e] text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-900/10 transition-all">
                            Simpan Anggaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Batas Anggaran -->
    <div id="modal-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/50 backdrop-blur-xs" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-edit text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Ubah Pos Anggaran</h3>
                    </div>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="form-edit" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Nama Kebutuhan / Pos Anggaran <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="edit_name" required maxlength="100"
                               class="w-full px-4 py-2.5 text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit_category_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Kategori Keuangan <span class="text-rose-500">*</span>
                        </label>
                        <select name="category_id" id="edit_category_id" required class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_amount" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Nominal Batas Baru (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-semibold text-sm">Rp</span>
                            <input type="number" name="amount" id="edit_amount" required min="1000" step="500"
                                   class="w-full pl-12 pr-4 py-3 text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeEditModal()" class="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-[#234066] hover:bg-[#1a304e] text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-900/10 transition-all">
                            Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Anggaran -->
    <div id="modal-delete" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/50 backdrop-blur-xs" onclick="closeDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-sm p-6 my-8 overflow-hidden text-center align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Hapus Anggaran?</h3>
                <p class="text-xs text-gray-500 mb-6">
                    Batas anggaran untuk pos <span id="delete-category-name" class="font-bold text-gray-800"></span> akan dihapus dari periode ini. Riwayat transaksi pengeluaran tidak akan hilang.
                </p>

                <form id="form-delete" method="POST" class="flex items-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-rose-600/20 transition-all">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts for Modals -->
    <script>
        function openCreateModal() {
            document.getElementById('create_name').value = '';
            document.getElementById('create_category_id').value = '';
            document.getElementById('create_amount').value = '';
            document.getElementById('modal-create').classList.remove('hidden');
            setTimeout(() => document.getElementById('create_name').focus(), 50);
        }

        function closeCreateModal() {
            document.getElementById('modal-create').classList.add('hidden');
        }

        function quickAddBudget(categoryId, categoryName) {
            const select = document.getElementById('create_category_id');
            if (select) {
                select.value = categoryId;
            }
            document.getElementById('create_name').value = categoryName;
            document.getElementById('create_amount').value = '';
            document.getElementById('modal-create').classList.remove('hidden');
            setTimeout(() => document.getElementById('create_amount').focus(), 50);
        }

        function openEditModal(budgetId, budgetName, categoryId, currentAmount) {
            const form = document.getElementById('form-edit');
            form.action = `/anggaran/${budgetId}`;
            document.getElementById('edit_name').value = budgetName;
            const catSelect = document.getElementById('edit_category_id');
            if (catSelect && categoryId) {
                catSelect.value = categoryId;
            }
            document.getElementById('edit_amount').value = Math.round(currentAmount);
            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('modal-edit').classList.add('hidden');
        }

        function openDeleteModal(budgetId, categoryName) {
            const form = document.getElementById('form-delete');
            form.action = `/anggaran/${budgetId}`;
            document.getElementById('delete-category-name').textContent = categoryName;
            document.getElementById('modal-delete').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('modal-delete').classList.add('hidden');
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateModal();
                closeEditModal();
                closeDeleteModal();
            }
        });
    </script>
</x-app-layout>
