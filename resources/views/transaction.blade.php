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

            <!-- Search Form -->
            <form action="{{ route('transaction.index') }}" method="GET" class="flex items-center gap-2">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <input name="search" value="{{ request('search') }}" type="text" placeholder="Cari transaksi..." 
                       class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none flex-1 md:w-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold shadow-md shadow-blue-200 whitespace-nowrap transition-all">
                    Cari
                </button>
            </form>
        </div>

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
                                        <span style="background-color: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }};" class="px-2 py-0.5 border rounded text-[10px] font-semibold">
                                            {{ $item->category->name ?? 'Lainnya' }}
                                        </span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $item->created_at->format('H:i') }} WIB</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0 text-right ml-2">
                            <p class="font-bold {{ $amountColor }} text-lg">
                                {{ $amountPrefix }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </p>
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Transaksi <span class="text-red-500">*</span></label>
                        <input type="text" name="description" required placeholder="Contoh: Gaji Bulanan, Beli Kopi, dll" 
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
        const sourceLabel = document.getElementById('label-savings-source');
        const destField = document.getElementById('field-savings-dest');
        const destSelect = document.getElementById('select-savings-dest');
        const feeField = document.getElementById('field-fee');

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
    });
</script>
