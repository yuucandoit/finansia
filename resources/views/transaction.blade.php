<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-slide-down">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-green-800 font-semibold">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-slide-down">
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-red-800 font-semibold">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="p-3 sm:p-4 flex flex-col md:flex-row justify-between gap-3 sm:gap-4">
            <!-- Filter Buttons -->
            <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-1 sm:pb-2">
                <a href="{{ route('transaction.index', array_merge(request()->except('page'), ['type' => null])) }}" 
                   class="{{ !request('type') ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition-all">
                    Semua
                </a>
                
                <a href="{{ route('transaction.index', array_merge(request()->except(['page', 'type']), ['type' => 'Cash In'])) }}" 
                   class="{{ request('type') == 'Cash In' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all">
                    Pemasukan
                </a>
                
                <a href="{{ route('transaction.index', array_merge(request()->except(['page', 'type']), ['type' => 'Cash Out'])) }}" 
                   class="{{ request('type') == 'Cash Out' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all">
                    Pengeluaran
                </a>
                
                <a href="{{ route('transaction.index', array_merge(request()->except(['page', 'type']), ['type' => 'Tabungan'])) }}" 
                   class="{{ request('type') == 'Tabungan' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all">
                    Tabungan
                </a>
            </div>

            <!-- Search & P2P Cycle Form -->
            <form action="{{ route('transaction.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <select name="cycle" onchange="this.form.submit()"
                        class="border border-indigo-200 bg-indigo-50/50 text-indigo-800 rounded-xl px-3 py-2 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="all" {{ ($selectedCycle ?? 'all') === 'all' ? 'selected' : '' }}>
                        Semua Periode (Payday Tgl {{ $paydayDay ?? 1 }})
                    </option>
                    @foreach(($availableCycles ?? []) as $cyc)
                        <option value="{{ $cyc['key'] }}" {{ ($selectedCycle ?? 'all') === $cyc['key'] ? 'selected' : '' }}>
                            P2P: {{ $cyc['label'] }}
                        </option>
                    @endforeach
                </select>
                <input name="search" value="{{ request('search') }}" type="text" placeholder="Cari transaksi..." 
                       class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none flex-1 md:w-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold shadow-md shadow-blue-200 whitespace-nowrap transition-all">
                    Cari
                </button>
            </form>
        </div>

        @if(!empty($activeCycleInfo))
            <div class="mx-3 sm:mx-4 mb-3 px-4 py-2.5 rounded-2xl bg-indigo-50 border border-indigo-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                <span class="font-semibold text-indigo-900">
                    📅 Menampilkan Siklus Payday (P2P): <strong>{{ $activeCycleInfo['label'] }}</strong>
                </span>
                <a href="{{ route('profile.edit') }}" class="font-bold text-indigo-700 hover:underline">
                    Ubah Tgl Payday (Tgl {{ $paydayDay ?? 1 }}) →
                </a>
            </div>
        @endif

        <div class="px-3 sm:px-4 pb-24">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base sm:text-lg font-bold text-gray-800">Transaksi</h3>
                <div class="relative">
                    <button onclick="toggleDropdown()" class="text-xs text-blue-600 font-semibold uppercase tracking-wider hover:underline flex items-center gap-1">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="dropdown-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                        <button onclick="toggleModal('modal-tambah'); toggleDropdown();" class="w-full text-left px-4 py-3 hover:bg-blue-50 flex items-center gap-3 transition-colors">
                            <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">Tambah Transaksi</span>
                        </button>
                        <button onclick="toggleModal('modal-import'); toggleDropdown();" class="w-full text-left px-4 py-3 hover:bg-emerald-50 flex items-center gap-3 transition-colors border-t border-gray-100">
                            <div class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">Import Transaksi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total Cash In & Cash Out Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                <!-- Cash In Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 rounded-2xl p-3.5 sm:p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-green-700 uppercase tracking-wide">Pemasukan</span>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-green-700 mt-1 truncate">
                        Rp {{ number_format($totalCashIn ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-green-600 mt-1">Halaman ini</p>
                </div>

                <!-- Cash Out Card -->
                <div class="bg-gradient-to-br from-red-50 to-orange-50 border border-red-100 rounded-2xl p-3.5 sm:p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-red-600 text-white rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-red-700 uppercase tracking-wide">Pengeluaran</span>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-red-700 mt-1 truncate">
                        Rp {{ number_format($totalCashOut ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-red-600 mt-1">Halaman ini</p>
                </div>
            </div>

            @php $lastDate = null; @endphp

            @foreach($transaction as $item)
                @php 
                    $currentDate = Carbon\Carbon::parse($item->transaction_date)->format('d F Y'); 
                    $isCashIn = ($item->type == 'Cash In'); 
                @endphp

                @if($currentDate !== $lastDate)
                    <div class="mt-6 mb-3">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            {{ $currentDate }}
                        </h3>
                    </div>
                    @php $lastDate = $currentDate; @endphp
                @endif

                @php
                    $isCashIn = ($item->type === 'Cash In');
                    $isTransfer = ($item->type === 'Transfer');
                    $isTabungan = ($item->type === 'Tabungan');
                    
                    $bgClass = 'bg-red-50';
                    $iconClass = 'fa-arrow-down text-red-600';
                    $amountColor = 'text-red-600';
                    $amountPrefix = '-';
                    
                    if ($isCashIn) {
                        $bgClass = 'bg-green-50';
                        $iconClass = 'fa-arrow-up text-green-600';
                        $amountColor = 'text-green-600';
                        $amountPrefix = '+';
                    } elseif ($isTransfer) {
                        $bgClass = 'bg-blue-50';
                        $iconClass = 'fa-exchange-alt text-blue-600';
                        if (str_contains(strtolower($item->description), 'dari')) {
                            $amountColor = 'text-green-600';
                            $amountPrefix = '+';
                        } else {
                            $amountColor = 'text-red-600';
                            $amountPrefix = '-';
                        }
                    } elseif ($isTabungan) {
                        $bgClass = 'bg-indigo-50';
                        $iconClass = 'fa-piggy-bank text-indigo-600';
                        if (str_contains(strtolower($item->description), 'dari')) {
                            $amountColor = 'text-green-600';
                            $amountPrefix = '+';
                        } else {
                            $amountColor = 'text-red-600';
                            $amountPrefix = '-';
                        }
                    }

                    $colorMap = [
                        'amber' => ['bg' => '#FFFBEB', 'text' => '#B45309', 'border' => '#FDE68A'],
                        'pink' => ['bg' => '#FDF2F8', 'text' => '#BE185D', 'border' => '#FBCFE8'],
                        'blue' => ['bg' => '#EFF6FF', 'text' => '#1D4ED8', 'border' => '#BFDBFE'],
                        'yellow' => ['bg' => '#FEFCE8', 'text' => '#A16207', 'border' => '#FEF08A'],
                        'purple' => ['bg' => '#FAF5FF', 'text' => '#6D28D9', 'border' => '#E9D5FF'],
                        'red' => ['bg' => '#FEF2F2', 'text' => '#B91C1C', 'border' => '#FEE2E2'],
                        'gray' => ['bg' => '#F9FAFB', 'text' => '#374151', 'border' => '#E5E7EB'],
                        'emerald' => ['bg' => '#ECFDF5', 'text' => '#047857', 'border' => '#A7F3D0'],
                        'indigo' => ['bg' => '#EEF2FF', 'text' => '#4338CA', 'border' => '#C7D2FE'],
                        'teal' => ['bg' => '#F0FDFA', 'text' => '#0F766E', 'border' => '#99F6E4'],
                        'orange' => ['bg' => '#FFF7ED', 'text' => '#C2410C', 'border' => '#FFEDD5'],
                    ];
                    $c = $colorMap[$item->category->color ?? 'gray'] ?? $colorMap['gray'];
                @endphp

                <div class="group bg-white border border-gray-100 rounded-2xl p-4 mb-3 shadow-sm hover:border-blue-200 transition-all">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center min-w-0 flex-1">
                            <div class="flex-shrink-0 w-12 h-12 {{ $bgClass }} rounded-xl flex items-center justify-center mr-4">
                                <i class="fas {{ $iconClass }} text-xl"></i>
                            </div>
                            
                            <div class="truncate"> 
                                <h4 class="font-bold text-gray-800 truncate">{{ $item->description }}</h4>
                                <div class="text-xs text-gray-400 truncate mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-semibold text-[10px] uppercase">
                                        {{ $item->savingAccount->name ?? 'Tanpa Rekening' }}
                                    </span>
                                    @if(!$isTransfer && !$isTabungan)
                                        @if($item->items->isNotEmpty())
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[10px] font-bold">
                                                Split ({{ $item->items->count() }} pos)
                                            </span>
                                            @foreach($item->items as $sItem)
                                                <span class="px-2 py-0.5 bg-gray-50 text-gray-600 border border-gray-200 rounded text-[10px]">
                                                    {{ $sItem->category?->name ?? 'Lainnya' }}: Rp {{ number_format($sItem->amount, 0, ',', '.') }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span style="background-color: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }};" class="px-2 py-0.5 border rounded text-[10px] font-semibold">
                                                {{ $item->category->name ?? 'Lainnya' }}
                                            </span>
                                        @endif
                                    @endif
                                    <span>•</span>
                                    <span>{{ $item->created_at->format('H:i') }} WIB</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0 flex items-center gap-3 ml-2">
                            <div class="text-right">
                                <p class="font-bold {{ $amountColor }} text-lg">
                                    {{ $amountPrefix }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                                </p>
                                @if(($item->fee ?? 0) > 0)
                                    <p class="text-[11px] text-gray-400">Biaya admin: Rp {{ number_format($item->fee, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                @php
                                    $txPayload = [
                                        'id' => $item->id,
                                        'description' => $item->description,
                                        'type' => $item->type,
                                        'amount' => (float) $item->amount,
                                        'fee' => (float) ($item->fee ?? 0),
                                        'savings_id' => $item->savings_id,
                                        'category_id' => $item->category_id,
                                        'budget_item_id' => $item->budget_item_id,
                                        'transaction_date' => \Carbon\Carbon::parse($item->transaction_date)->format('Y-m-d'),
                                    ];
                                @endphp
                                <button type="button"
                                    onclick='openEditTxModal(@json($txPayload))'
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                                    title="Edit Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button type="button"
                                    onclick="openDeleteTxModal({{ $item->id }}, {{ json_encode($item->description) }}, '{{ number_format($item->amount, 0, ',', '.') }}')"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                                    title="Hapus Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            @if($transaction->hasPages())
                <div class="mt-8 mb-4">
                    <!-- Pagination Info -->
                    <div class="flex justify-center mb-4">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold text-blue-600">{{ $transaction->firstItem() }}</span> 
                            sampai <span class="font-semibold text-blue-600">{{ $transaction->lastItem() }}</span> 
                            dari <span class="font-semibold text-blue-600">{{ $transaction->total() }}</span> transaksi
                        </p>
                    </div>

                    <!-- Pagination Links -->
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                        {{-- Previous Button --}}
                        @if ($transaction->onFirstPage())
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-xl cursor-not-allowed flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </span>
                        @else
                            <a href="{{ $transaction->previousPageUrl() }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center gap-1 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($transaction->currentPage() - 2, 1);
                            $end = min($start + 4, $transaction->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        {{-- First Page --}}
                        @if($start > 1)
                            <a href="{{ $transaction->url(1) }}" class="w-10 h-10 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center justify-center font-medium">
                                1
                            </a>
                            @if($start > 2)
                                <span class="text-gray-400 px-2">...</span>
                            @endif
                        @endif

                        {{-- Page Numbers Loop --}}
                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $transaction->currentPage())
                                <span class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl flex items-center justify-center font-bold shadow-md shadow-blue-200">
                                    {{ $i }}
                                </span>
                            @else
                                <a href="{{ $transaction->url($i) }}" class="w-10 h-10 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center justify-center font-medium">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor

                        {{-- Last Page --}}
                        @if($end < $transaction->lastPage())
                            @if($end < $transaction->lastPage() - 1)
                                <span class="text-gray-400 px-2">...</span>
                            @endif
                            <a href="{{ $transaction->url($transaction->lastPage()) }}" class="w-10 h-10 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center justify-center font-medium">
                                {{ $transaction->lastPage() }}
                            </a>
                        @endif

                        {{-- Next Button --}}
                        @if ($transaction->hasMorePages())
                            <a href="{{ $transaction->nextPageUrl() }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center gap-1 font-medium">
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        @else
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-xl cursor-not-allowed flex items-center gap-1">
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </main>
    <!-- Modal Tambah Transaksi -->
    <div id="modal-tambah" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <!-- Backdrop -->
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleModal('modal-tambah')"></div>

            <!-- Center Modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Tambah Transaksi</h3>
                        <p class="text-sm text-gray-500 mt-1">Catat transaksi keuangan Anda</p>
                    </div>
                    <button onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Tabs Header -->
                <div class="flex border-b border-gray-100 mb-5 overflow-x-auto no-scrollbar">
                    <button type="button" onclick="switchFormTab('Cash Out')" id="tab-cash-out" class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-all whitespace-nowrap px-3">
                        💸 Pengeluaran
                    </button>
                    <button type="button" onclick="switchFormTab('Cash In')" id="tab-cash-in" class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-all whitespace-nowrap px-3">
                        💰 Pemasukan
                    </button>
                    <button type="button" onclick="switchFormTab('Transfer')" id="tab-transfer" class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-all whitespace-nowrap px-3">
                        🔄 Transfer
                    </button>
                    <button type="button" onclick="switchFormTab('Tabungan')" id="tab-tabungan" class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-all whitespace-nowrap px-3">
                        🎯 Tabungan
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('transaction.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Hidden Type Input -->
                    <input type="hidden" name="type" id="form-type-input" value="Cash Out">

                    <!-- Deskripsi -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Deskripsi Transaksi <span class="text-red-500">*</span></label>
                            <span id="smart-rule-hint" class="hidden text-[11px] font-bold px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700"></span>
                        </div>
                        <input type="text" name="description" id="form-description-input" required placeholder="Contoh: Gaji Bulanan, Beli Kopi, dll"
                               oninput="debouncedAutoSuggestCategory(this.value)"
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                    </div>

                    <!-- Nominal & Tanggal -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">Rp</span>
                                <input type="text" id="nominal-formatted" required placeholder="0" 
                                       class="w-full border-gray-300 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border font-bold text-base transition-all">
                                <input type="hidden" name="amount" id="nominal-raw" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}"
                                   class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                        </div>
                    </div>

                    <!-- Toggle Split Transaksi -->
                    <div id="split-toggle-wrapper" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-indigo-50/60 border border-indigo-100">
                        <div>
                            <p class="text-xs font-bold text-indigo-900">Pecah Kategori (Split Transaksi)</p>
                            <p class="text-[11px] text-indigo-600">Bagi 1 struk ke beberapa kategori / pos anggaran</p>
                        </div>
                        <button type="button" id="btn-toggle-split" onclick="toggleSplitMode()"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition">
                            Aktifkan Split
                        </button>
                    </div>

                    <!-- Split Builder Box -->
                    <div id="split-builder-box" class="hidden space-y-3 p-3.5 rounded-2xl bg-gray-50 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Rincian Split Transaksi</span>
                            <button type="button" onclick="addSplitRow()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                + Tambah Baris
                            </button>
                        </div>
                        <div id="split-rows-container" class="space-y-2.5"></div>
                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 text-xs">
                            <span class="text-gray-500">Total Split: <strong id="split-sum-label" class="text-gray-800">Rp 0</strong></span>
                            <span id="split-diff-label" class="font-bold text-gray-500"></span>
                        </div>
                    </div>

                    <!-- Pos Anggaran / Amplop (Khusus Pengeluaran) -->
                    <div id="field-budget-item" class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-700">Pos Anggaran / Amplop (Opsional)</label>
                        <select name="budget_item_id" id="form-budget-item-id" onchange="onBudgetItemChange(this)" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                            <option value="">-- Tanpa Pos / Pengeluaran Umum --</option>
                            @if(isset($budgetItems))
                                @foreach($budgetItems as $bItem)
                                    <option value="{{ $bItem->id }}" data-category-id="{{ $bItem->category_id }}">
                                        🎯 {{ $bItem->name }} (Sisa: Rp {{ number_format(max(0, $bItem->remaining), 0, ',', '.') }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <p class="text-[11px] text-gray-400">Pilih pos kebutuhan untuk menghubungkan transaksi langsung ke amplop anggaran.</p>
                    </div>

                    <!-- Kategori (Dinamis) -->
                    <div id="field-category">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" id="form-category-id" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rekening Sumber / Penerima -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="field-savings-source">
                            <label id="label-savings-source" class="block text-sm font-semibold text-gray-700 mb-2">Sumber Rekening / Dompet <span class="text-red-500">*</span></label>
                            <select name="savings_id" required class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                                <option value="">Pilih Rekening</option>
                                @foreach($saving as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->current_balance ?? 0, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rekening Tujuan (Untuk Transfer/Tabungan) -->
                        <div id="field-savings-dest" class="hidden">
                            <label id="label-savings-dest" class="block text-sm font-semibold text-gray-700 mb-2">Ke Rekening (Tujuan) <span class="text-red-500">*</span></label>
                            <select name="destination_savings_id" id="select-savings-dest" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                                <option value="">Pilih Rekening Tujuan</option>
                                @foreach($saving as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->current_balance ?? 0, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Biaya Admin (Untuk Transfer/Tabungan) -->
                    <div id="field-fee" class="hidden">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Biaya Admin (Opsional)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">Rp</span>
                            <input type="text" id="fee-formatted" placeholder="0" 
                                   class="w-full border-gray-300 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border font-semibold text-base transition-all">
                            <input type="hidden" name="fee" id="fee-raw">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="toggleModal('modal-tambah')" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-5 rounded-xl font-semibold transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-5 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all">
                            💾 Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <!-- Modal Import E-Statement -->
    <div id="modal-import" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <!-- Backdrop -->
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleModal('modal-import')"></div>

            <!-- Center Modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block w-full max-w-lg p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <!-- Close Button -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Import E-Statement Bank</h3>
                        <p class="text-sm text-gray-500">Upload file e-statement dari bank Anda yang sudah terenkripsi</p>
                    </div>
                    <button onclick="toggleModal('modal-import')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('transaction.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    
                    <!-- Rekening Tabungan (Required) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Rekening Tabungan <span class="text-red-500">*</span>
                        </label>
                        <select name="savings_id" id="savings-select" required 
                                class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 border transition-all">
                            <option value="">Pilih Rekening Tabungan</option>
                            @foreach($saving as $s)
                                <option value="{{ $s->id }}" data-bank="{{ strtolower($s->bank->name ?? '') }}">
                                    {{ $s->bank->name ?? 'Unknown' }} - {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">⚠️ Pilih rekening yang sesuai dengan file e-statement</p>
                    </div>

                    <!-- File Upload Area -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            File E-Statement <span class="text-red-500">*</span>
                        </label>
                        <label for="file-upload-import" class="block w-full border-2 border-dashed border-emerald-300 bg-emerald-50 rounded-2xl p-6 cursor-pointer hover:bg-emerald-100 hover:border-emerald-400 transition-all group">
                            <input id="file-upload-import" name="file" type="file" accept=".xlsx,.xls,.csv" required class="hidden" onchange="updateImportFileName(this)">
                            <div class="text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-emerald-600 mb-2 group-hover:scale-110 transition-transform">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span id="import-file-name" class="text-emerald-700 font-semibold text-sm">Klik untuk pilih file</span>
                                <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Excel terenkripsi)</p>
                            </div>
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Password File <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required placeholder="Masukkan password file e-statement" 
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 border transition-all">
                        <p class="text-xs text-gray-500 mt-1">🔐 Password yang digunakan bank untuk enkripsi file</p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <div class="text-xs text-blue-800">
                                <p class="font-semibold mb-1">Bank yang Didukung:</p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li>Bank Mandiri (.xlsx terenkripsi)</li>
                                    <li>BCA (coming soon)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="toggleModal('modal-import')" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-5 rounded-xl font-semibold transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-3 px-5 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all">
                            📤 Import Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Transaksi -->
    <div id="modal-edit" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleModal('modal-edit')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-t-3xl sm:rounded-3xl animate-slide-up sm:animate-none">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Edit Transaksi</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Perubahan otomatis menyesuaikan saldo rekening</p>
                    </div>
                    <button type="button" onclick="toggleModal('modal-edit')" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="form-edit-tx" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Transaksi</label>
                            <select name="type" id="edit-type" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="Cash Out">Pengeluaran (Cash Out)</option>
                                <option value="Cash In">Pemasukan (Cash In)</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Tabungan">Tabungan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                            <input type="date" name="transaction_date" id="edit-date" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi</label>
                        <input type="text" name="description" id="edit-description" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nominal (Rp)</label>
                            <input type="number" name="amount" id="edit-amount" min="0" step="0.01" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Biaya Admin / Fee (Opsional)</label>
                            <input type="number" name="fee" id="edit-fee" min="0" step="0.01" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rekening / Tabungan</label>
                            <select name="savings_id" id="edit-savings-id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                @foreach($saving as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Rp {{ number_format($item->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori</label>
                            <select name="category_id" id="edit-category-id" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 flex gap-3">
                        <button type="button" onclick="toggleModal('modal-edit')" class="flex-1 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
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
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="toggleModal('modal-delete-tx')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-center align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Hapus Transaksi Ini?</h3>
                <p class="text-sm text-gray-500 mb-2">Transaksi <strong id="delete-tx-desc" class="text-gray-800"></strong> sebesar <strong id="delete-tx-amount" class="text-red-600"></strong> akan dihapus.</p>
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-2.5 mb-5">
                    Saldo rekening terkait akan otomatis dikembalikan sesuai nominal transaksi ini.
                </p>

                <form id="form-delete-tx" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="toggleModal('modal-delete-tx')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold text-sm transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-bold text-sm shadow-lg shadow-red-200 transition-all">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<style>
    /* Menghilangkan scrollbar tapi tetap bisa scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    /* Animasi muncul dari bawah untuk mobile */
    @keyframes slide-up {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
    
    /* Animasi untuk flash messages */
    @keyframes slide-down {
        from { 
            opacity: 0;
            transform: translateY(-20px); 
        }
        to { 
            opacity: 1;
            transform: translateY(0); 
        }
    }
    .animate-slide-down {
        animation: slide-down 0.3s ease-out;
    }
</style>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Kunci scroll layar saat modal buka
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Aktifkan kembali scroll
        }
    }

    function toggleDropdown() {
        const dropdown = document.getElementById('dropdown-menu');
        dropdown.classList.toggle('hidden');
    }

    // Menutup dropdown jika klik di luar
    window.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdown-menu');
        const button = event.target.closest('button[onclick="toggleDropdown()"]');
        
        if (!button && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Menutup modal jika menekan tombol Escape di keyboard
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('modal-tambah').classList.add('hidden');
            document.getElementById('modal-import').classList.add('hidden');
            document.getElementById('dropdown-menu').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });

    // Update filename saat file dipilih
    function updateFileName(input) {
        const fileName = input.files[0]?.name || 'Klik untuk pilih file';
        document.getElementById('file-name').textContent = fileName;
    }

    // Update filename untuk import modal
    function updateImportFileName(input) {
        const fileName = input.files[0]?.name || 'Klik untuk pilih file';
        document.getElementById('import-file-name').textContent = fileName;
    }

    // Dynamic categories filtering based on type
    function filterCategories(type) {
        const select = document.getElementById('form-category-id');
        if (!select) return;
        const options = select.querySelectorAll('option');
        let hasVisible = false;
        options.forEach(opt => {
            const catType = opt.getAttribute('data-type');
            if (!catType) {
                opt.style.display = 'block'; // "Pilih Kategori" option
            } else if (catType === type) {
                opt.style.display = 'block';
                if (!hasVisible) {
                    select.value = opt.value;
                    hasVisible = true;
                }
            } else {
                opt.style.display = 'none';
            }
        });
        if (!hasVisible) {
            select.value = "";
        }
    }

    // Switch form tabs dynamically
    function switchFormTab(type) {
        // Set hidden type input
        document.getElementById('form-type-input').value = type;
        
        // Update active tab styles
        const tabs = ['Cash Out', 'Cash In', 'Transfer', 'Tabungan'];
        tabs.forEach(t => {
            const btnId = 'tab-' + t.toLowerCase().replace(' ', '-');
            const btn = document.getElementById(btnId);
            if (!btn) return;
            if (t === type) {
                btn.classList.add('border-blue-600', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-400');
            } else {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-400');
            }
        });

        // Dynamic fields display logic
        const categoryField = document.getElementById('field-category');
        const budgetItemField = document.getElementById('field-budget-item');
        const sourceLabel = document.getElementById('label-savings-source');
        const destField = document.getElementById('field-savings-dest');
        const destSelect = document.getElementById('select-savings-dest');
        const feeField = document.getElementById('field-fee');

        if (budgetItemField) {
            if (type === 'Cash Out') {
                budgetItemField.classList.remove('hidden');
            } else {
                budgetItemField.classList.add('hidden');
                const bSelect = document.getElementById('form-budget-item-id');
                if (bSelect) bSelect.value = '';
            }
        }

        if (type === 'Cash Out') {
            categoryField.classList.remove('hidden');
            filterCategories('Cash Out');
            sourceLabel.textContent = 'Sumber Rekening / Dompet';
            destField.classList.add('hidden');
            destSelect.removeAttribute('required');
            feeField.classList.add('hidden');
        } else if (type === 'Cash In') {
            categoryField.classList.remove('hidden');
            filterCategories('Cash In');
            sourceLabel.textContent = 'Rekening Penerima';
            destField.classList.add('hidden');
            destSelect.removeAttribute('required');
            feeField.classList.add('hidden');
        } else if (type === 'Transfer') {
            categoryField.classList.add('hidden');
            sourceLabel.textContent = 'Dari Rekening (Asal)';
            destField.classList.remove('hidden');
            document.getElementById('label-savings-dest').textContent = 'Ke Rekening (Tujuan)';
            destSelect.setAttribute('required', 'required');
            feeField.classList.remove('hidden');
        } else if (type === 'Tabungan') {
            categoryField.classList.add('hidden');
            sourceLabel.textContent = 'Dari Rekening (Sumber)';
            destField.classList.remove('hidden');
            document.getElementById('label-savings-dest').textContent = 'Ke Akun Tabungan (Tujuan)';
            destSelect.setAttribute('required', 'required');
            feeField.classList.remove('hidden');
        }
    }

    function onBudgetItemChange(select) {
        const selected = select.options[select.selectedIndex];
        const catId = selected.getAttribute('data-category-id');
        if (catId) {
            const catSelect = document.getElementById('form-category-id');
            if (catSelect) {
                catSelect.value = catId;
            }
        }
    }

    // Auto format rupiah helper
    function setupCurrencyFormatting(formattedId, rawId) {
        const formattedInput = document.getElementById(formattedId);
        const rawInput = document.getElementById(rawId);
        
        if (formattedInput && rawInput) {
            formattedInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value === '') {
                    this.value = '';
                    rawInput.value = '';
                    return;
                }
                rawInput.value = value;
                this.value = new Intl.NumberFormat('id-ID').format(value);
            });
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

        toggleModal('modal-edit');
    }

    function openDeleteTxModal(id, description, formattedAmount) {
        const form = document.getElementById('form-delete-tx');
        form.action = '/transaksi/' + id;

        document.getElementById('delete-tx-desc').textContent = description;
        document.getElementById('delete-tx-amount').textContent = 'Rp ' + formattedAmount;

        toggleModal('modal-delete-tx');
    }

    // On Load
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('add')) {
            toggleModal('modal-tambah');
        }
        
        // Form tabs initialization
        switchFormTab('Cash Out');
        
        // Set up formatting
        setupCurrencyFormatting('nominal-formatted', 'nominal-raw');
        setupCurrencyFormatting('fee-formatted', 'fee-raw');

        const nominalRaw = document.getElementById('nominal-raw');
        const nominalFmt = document.getElementById('nominal-formatted');
        if (nominalFmt) {
            nominalFmt.addEventListener('input', updateSplitSummary);
        }
        if (nominalRaw) {
            nominalRaw.addEventListener('change', updateSplitSummary);
        }
    });

    // Smart Rules Auto-Suggest
    let _suggestTimer = null;
    function debouncedAutoSuggestCategory(desc) {
        clearTimeout(_suggestTimer);
        const hintEl = document.getElementById('smart-rule-hint');
        if (!desc || desc.trim().length < 2 || window._splitModeEnabled) {
            if (hintEl) hintEl.classList.add('hidden');
            return;
        }
        _suggestTimer = setTimeout(() => {
            const dateInput = document.querySelector('#modal-tambah input[name="transaction_date"]');
            const dateVal = dateInput ? dateInput.value : '';
            fetch(`{{ route('category.rules.match') }}?description=${encodeURIComponent(desc)}&date=${encodeURIComponent(dateVal)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !data.matched || !data.category_id) {
                    if (hintEl) hintEl.classList.add('hidden');
                    return;
                }
                const catSelect = document.getElementById('form-category-id');
                if (catSelect) {
                    catSelect.value = String(data.category_id);
                }
                const budgetSelect = document.getElementById('form-budget-item-id');
                if (budgetSelect && data.budget_item_id) {
                    budgetSelect.value = String(data.budget_item_id);
                }
                if (hintEl) {
                    hintEl.textContent = `⚡ Auto: ${data.category_name || 'Cocok'}`;
                    hintEl.classList.remove('hidden');
                }
            })
            .catch(() => {});
        }, 300);
    }

    // Split Transaksi Builder
    window._splitModeEnabled = false;
    window._splitRowIndex = 0;

    function toggleSplitMode() {
        window._splitModeEnabled = !window._splitModeEnabled;
        const box = document.getElementById('split-builder-box');
        const btn = document.getElementById('btn-toggle-split');
        const fieldBudget = document.getElementById('field-budget-item');
        const fieldCategory = document.getElementById('field-category');
        const container = document.getElementById('split-rows-container');

        if (window._splitModeEnabled) {
            box.classList.remove('hidden');
            btn.textContent = 'Batalkan Split';
            if (fieldBudget) fieldBudget.classList.add('hidden');
            if (fieldCategory) fieldCategory.classList.add('hidden');
            if (container && container.children.length === 0) {
                addSplitRow();
                addSplitRow();
            }
            updateSplitSummary();
        } else {
            box.classList.add('hidden');
            btn.textContent = 'Aktifkan Split';
            if (fieldBudget) fieldBudget.classList.remove('hidden');
            if (fieldCategory) fieldCategory.classList.remove('hidden');
            if (container) container.innerHTML = '';
        }
    }

    function addSplitRow() {
        const container = document.getElementById('split-rows-container');
        if (!container) return;

        const idx = window._splitRowIndex++;
        const catOptions = document.getElementById('form-category-id')?.innerHTML || '<option value="">Pilih Kategori</option>';
        const budgetOptions = document.getElementById('form-budget-item-id')?.innerHTML || '<option value="">-- Tanpa Pos --</option>';

        const row = document.createElement('div');
        row.className = 'split-row p-2.5 bg-white rounded-xl border border-gray-200 space-y-2';
        row.innerHTML = `
            <div class="grid grid-cols-12 gap-2 items-center">
                <select name="splits[${idx}][category_id]" required class="col-span-5 text-xs border border-gray-200 rounded-lg px-2 py-2 bg-white">
                    ${catOptions}
                </select>
                <input type="number" name="splits[${idx}][amount]" min="1" step="1" required placeholder="Nominal"
                       oninput="updateSplitSummary()"
                       class="split-amount-input col-span-5 text-xs border border-gray-200 rounded-lg px-2.5 py-2 font-semibold">
                <button type="button" onclick="removeSplitRow(this)" class="col-span-2 text-xs font-bold text-rose-500 hover:text-rose-700 py-1.5">
                    Hapus
                </button>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <select name="splits[${idx}][budget_item_id]" class="text-[11px] border border-gray-200 rounded-lg px-2 py-1.5 bg-white">
                    ${budgetOptions}
                </select>
                <input type="text" name="splits[${idx}][description]" placeholder="Catatan item (opsional)"
                       class="text-[11px] border border-gray-200 rounded-lg px-2 py-1.5">
            </div>
        `;
        container.appendChild(row);
        updateSplitSummary();
    }

    function removeSplitRow(btn) {
        const row = btn.closest('.split-row');
        if (row) row.remove();
        updateSplitSummary();
    }

    function updateSplitSummary() {
        if (!window._splitModeEnabled) return;
        const target = parseFloat(document.getElementById('nominal-raw')?.value || '0') || 0;
        let sum = 0;
        document.querySelectorAll('.split-amount-input').forEach(inp => {
            sum += parseFloat(inp.value || '0') || 0;
        });

        const sumEl = document.getElementById('split-sum-label');
        const diffEl = document.getElementById('split-diff-label');
        if (sumEl) sumEl.textContent = 'Rp ' + sum.toLocaleString('id-ID');

        if (diffEl) {
            const diff = target - sum;
            if (target > 0 && Math.abs(diff) < 0.01) {
                diffEl.textContent = '✓ Pas dengan total';
                diffEl.className = 'font-bold text-emerald-600';
            } else if (diff > 0) {
                diffEl.textContent = 'Sisa belum dibagi: Rp ' + diff.toLocaleString('id-ID');
                diffEl.className = 'font-bold text-amber-600';
            } else if (diff < 0) {
                diffEl.textContent = 'Kelebihan: Rp ' + Math.abs(diff).toLocaleString('id-ID');
                diffEl.className = 'font-bold text-rose-600';
            } else {
                diffEl.textContent = '';
            }
        }
    }
</script>
