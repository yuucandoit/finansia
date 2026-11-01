<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('future-planner.index') }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition" title="Kembali ke Semua Rencana">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                            {{ $futurePlan->title }}
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $futurePlan->status_badge_class }}">
                            {{ $futurePlan->status_label }}
                        </span>
                    </div>
                    @if($futurePlan->description)
                        <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ $futurePlan->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <!-- Widget Indikator Pengguna Aktif (Lingkaran Huruf Pertama) -->
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-2xl border border-gray-200 shadow-2xs">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="hidden md:inline text-[11px] font-semibold text-emerald-700">Aktif</span>
                    </div>

                    <!-- Container Avatar Bulat Huruf Pertama Pengguna Aktif -->
                    <div id="activeUsersList" class="flex items-center -space-x-1.5 hover:space-x-1 transition-all">
                        @forelse($activeUsers as $u)
                            <div class="relative group user-avatar-badge" data-user-id="{{ $u['id'] }}">
                                <div class="w-8 h-8 rounded-full {{ $u['color'] }} text-white font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-2xs transition transform hover:scale-110 cursor-pointer select-none">
                                    {{ $u['initial'] }}
                                </div>
                                <!-- Dot hijau indikator online -->
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>

                                <!-- Tooltip info user -->
                                <div class="absolute right-0 top-full mt-2 hidden group-hover:flex flex-col items-center z-50 pointer-events-none">
                                    <div class="bg-gray-900 text-white text-[11px] rounded-lg py-1 px-2.5 whitespace-nowrap shadow-xl">
                                        <p class="font-semibold">{{ $u['name'] }} {{ $u['is_current'] ? '(Anda)' : '' }}</p>
                                        <p class="text-[10px] text-emerald-300 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                                            {{ $u['role_label'] }} • Sedang aktif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-2xs" title="Hanya Anda yang saat ini membuka rencana ini">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endforelse
                    </div>

                    <div class="h-4 w-px bg-gray-200 mx-0.5"></div>

                    <!-- Tombol Kolaborasi & Kode Unik -->
                    <button type="button"
                            onclick="openCollaborationModal()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition"
                            title="Buka Pengaturan Anggota & Kode Unik">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="font-mono text-[11px] font-bold">{{ $futurePlan->share_code }}</span>
                        <span class="text-[10px] font-bold bg-indigo-200 text-indigo-800 px-1.5 py-0.2 rounded-md">{{ $allMembers->count() }}</span>
                    </button>
                </div>

                <!-- Ekspor Data Dropdown -->
                <div class="relative inline-block text-left" id="exportDropdownContainer">
                    <button type="button"
                            onclick="toggleExportMenu(event)"
                            id="btnExportDropdown"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs rounded-xl border border-gray-200 shadow-2xs transition"
                            title="Ekspor data item rencana">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Ekspor</span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="exportMenu" class="hidden absolute right-0 mt-1.5 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50 text-xs">
                        <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Format Ekspor</div>
                        <a href="{{ route('future-planner.export', [$futurePlan, 'format' => 'xlsx']) }}" class="flex items-center gap-2.5 px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-semibold block">Excel (.xlsx)</span>
                                <span class="text-[10px] text-gray-400 font-normal">Spreadsheet OpenXML rapi</span>
                            </div>
                        </a>
                        <a href="{{ route('future-planner.export', [$futurePlan, 'format' => 'csv']) }}" class="flex items-center gap-2.5 px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium transition">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div>
                                <span class="font-semibold block">CSV (.csv)</span>
                                <span class="text-[10px] text-gray-400 font-normal">Teks terpisah koma UTF-8 BOM</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Tombol Impor Data -->
                <button type="button"
                        onclick="openImportModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs rounded-xl border border-gray-200 shadow-2xs transition"
                        title="Impor item kebutuhan dari file spreadsheet">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Impor</span>
                </button>

                <!-- Toggle Full Width Button -->
                <button type="button" onclick="toggleFullWidthMode()" id="btnToggleWidth" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs rounded-xl border border-gray-200 shadow-2xs transition" title="Ganti Mode Tampilan Lebar">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <span id="labelToggleWidth">Mode Boxed</span>
                </button>

                <button type="button" onclick="focusQuickAddRow()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Tambah Item di Tabel</span>
                </button>
            </div>
        </div>
    </x-slot>

    <!-- WRAPPER UTAMA: FULL WIDTH SECARA DEFAULT UNTUK SPREADSHEET / OUTLINE TREE VIEW -->
    <div class="py-6">
        <div id="plannerContainer" class="w-full px-3 sm:px-6 lg:px-8 xl:px-10 space-y-5 transition-all duration-300">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between text-sm shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm font-semibold">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center justify-between text-sm shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-sm font-semibold">&times;</button>
                </div>
            @endif

            <!-- KPI Summary Cards (Full-Width Responsive Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Grand Total Kebutuhan</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            💰
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-indigo-600" id="card_total_estimated">
                            Rp {{ number_format($totalEstimated, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Akumulasi {{ $totalItemsCount }} item (termasuk sub-item)</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Terbeli / Selesai</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            ✅
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-emerald-600" id="card_total_completed">
                            Rp {{ number_format($totalCompleted, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5" id="card_completed_count">
                            {{ $completedItemsCount }} dari {{ $totalItemsCount }} item terbeli
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Kebutuhan Dana</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                            ⏳
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-amber-600" id="card_total_remaining">
                            Rp {{ number_format($totalRemaining, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">Dana yang masih harus disiapkan</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Progres Belanja</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                            📊
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-bold text-gray-900" id="card_progress_text">{{ $progressPercentage }}%</span>
                            <span class="text-xs text-gray-400" id="card_items_ratio">{{ $completedItemsCount }}/{{ $totalItemsCount }} item</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-2 overflow-hidden">
                            <div id="card_progress_bar" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW MODE SELECTOR: TABEL / SPREADSHEET vs GANTT CHART (TIMELINE) -->
            <div class="flex items-center justify-between border-b border-gray-200 pt-1">
                <div class="flex items-center gap-1">
                    <button type="button"
                            onclick="switchViewMode('table')"
                            id="tabBtnTable"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-indigo-600 text-indigo-700 bg-indigo-50/50 rounded-t-xl">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Daftar Tabel & Rincian</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-800 font-mono">{{ $totalItemsCount }}</span>
                    </button>

                    <button type="button"
                            onclick="switchViewMode('gantt')"
                            id="tabBtnGantt"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Gantt Chart (Linimasa)</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">
                            Linimasa 📊
                        </span>
                    </button>
                </div>
            </div>

            <!-- WRAPPER MODE 1: TAMPILAN TABEL SPREADSHEET -->
            <div id="tableViewContainer" class="space-y-5">
                <!-- Toolbar Filter, Search & Petunjuk Hierarki Cabang -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0">
                    <a href="{{ route('future-planner.show', $futurePlan) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ empty($filter) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Item ({{ $totalItemsCount }})
                    </a>
                    <a href="{{ route('future-planner.show', [$futurePlan, 'filter' => 'pending']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $filter === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Belum Terbeli ({{ $totalItemsCount - $completedItemsCount }})
                    </a>
                    <a href="{{ route('future-planner.show', [$futurePlan, 'filter' => 'completed']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $filter === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Sudah Terbeli ({{ $completedItemsCount }})
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="itemSearchInput" onkeyup="filterItemsTable()" placeholder="Cari item di tabel..."
                               class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-400">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <button type="button" onclick="toggleAllBranches(true)" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition text-xs flex items-center gap-1 shrink-0 border border-gray-200" title="Buka Semua Cabang">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span class="hidden md:inline font-medium">Buka Semua</span>
                    </button>
                    <button type="button" onclick="toggleAllBranches(false)" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition text-xs flex items-center gap-1 shrink-0 border border-gray-200" title="Tutup Semua Cabang">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="hidden md:inline font-medium">Tutup Cabang</span>
                    </button>
                </div>
            </div>

            <!-- Petunjuk Pintar Parent-Child -->
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl px-4 py-2.5 flex items-center justify-between text-xs text-indigo-900 gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-indigo-700">💡 Cara Buat Parent-Child:</span>
                    <span>1. Klik tombol <strong>+ Sub</strong> pada baris induk untuk buat anak langsung.</span>
                    <span class="text-gray-300">|</span>
                    <span>2. Gunakan tombol <strong>↳ Sub</strong> / <strong>↰ Utama</strong> pada baris untuk geser tingkat cabang.</span>
                    <span class="text-gray-300">|</span>
                    <span>3. Pilih induk di kolom <strong>Induk</strong> pada baris paling bawah.</span>
                </div>
            </div>

            <!-- TABEL SPREADSHEET OUTLINE PARENT-CHILD (FULL WIDTH & INLINE INPUT) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse min-w-[950px]" id="itemsTable">
                        <thead>
                            <tr class="bg-gray-50/90 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-3 text-center w-12">Status</th>
                                <th class="py-3 px-4 min-w-[340px]">Hierarki Item & Cabang (Parent - Child)</th>
                                <th class="py-3 px-3 text-center w-20">Qty</th>
                                <th class="py-3 px-4 text-right w-36">Harga Satuan</th>
                                <th class="py-3 px-4 text-right w-44">Total Harga</th>
                                <th class="py-3 px-4 min-w-[160px]">Catatan / Spesifikasi</th>
                                <th class="py-3 px-4 text-center w-48">Aksi Hirarki & Edit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs" id="itemsTableBody">
                            @foreach($items as $index => $item)
                                @php
                                    $depth = $item->depth ?? 0;
                                    $hasChildren = $item->hasChildren();
                                    $subtotal = $item->effective_subtotal;
                                    $isTarget = $hasChildren && $item->subtotal > 0;
                                    $overBudget = $isTarget ? $item->over_budget_amount : 0;
                                    $isCompleted = $item->is_completed;
                                    // Ambil item sebelumnya untuk opsi quick indent
                                    $prevItem = $index > 0 ? $items[$index - 1] : null;
                                @endphp

                                <!-- Baris Tampilan Item -->
                                <tr class="item-row hover:bg-indigo-50/20 transition {{ $isCompleted ? 'bg-emerald-50/20' : ($hasChildren ? 'bg-slate-50/40' : '') }}"
                                    data-id="{{ $item->id }}"
                                    data-parent-id="{{ $item->parent_id ?? '' }}"
                                    data-depth="{{ $depth }}"
                                    id="row-item-{{ $item->id }}">

                                    <!-- Checkbox Checklist -->
                                    <td class="py-3 px-3 text-center align-middle">
                                        <input type="checkbox"
                                               onchange="toggleItemStatus({{ $item->id }}, this)"
                                               {{ $isCompleted ? 'checked' : '' }}
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer transition">
                                    </td>

                                    <!-- Title with Authentic Multi-Level Tree Lines -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="flex items-center gap-1.5" style="padding-left: {{ $depth * 28 }}px;">
                                            @if($depth > 0)
                                                <div class="flex items-center text-indigo-400 font-mono text-xs select-none mr-1">
                                                    @for($i = 1; $i < $depth; $i++)
                                                        <span class="text-gray-300 mr-2">│</span>
                                                    @endfor
                                                    <span class="text-indigo-500 font-bold">└──</span>
                                                </div>
                                            @endif

                                            @if($hasChildren)
                                                <button type="button" onclick="toggleBranchRow({{ $item->id }})" class="p-0.5 text-gray-500 hover:text-indigo-600 rounded transition" title="Buka/Tutup Sub-cabang">
                                                    <svg id="chevron-{{ $item->id }}" class="w-3.5 h-3.5 transition-transform duration-200 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-900 border border-indigo-200">
                                                    📁 Induk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $depth === 0 ? 'bg-slate-100 text-slate-800' : 'bg-gray-100 text-gray-600' }}">
                                                    L{{ $depth + 1 }}
                                                </span>
                                            @endif

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-medium text-gray-900 item-title {{ $isCompleted ? 'line-through text-gray-400' : ($hasChildren ? 'font-bold text-indigo-950' : '') }}" id="title-item-{{ $item->id }}">
                                                        {{ $item->title }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-0.5" id="date-badge-item-{{ $item->id }}">
                                                    @if($item->start_date || $item->end_date)
                                                        <button type="button"
                                                                onclick="openDateEditModal({{ $item->id }})"
                                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $item->timeline_status === 'overdue' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($item->timeline_status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700') }} hover:opacity-80 transition"
                                                                title="Ubah Jadwal Linimasa Gantt Chart">
                                                            <span>📅</span>
                                                            <span>{{ $item->start_date ? $item->start_date->format('d M') : '?' }} - {{ $item->end_date ? $item->end_date->format('d M Y') : '?' }}</span>
                                                            @if($item->duration_days)
                                                                <span class="font-bold">({{ $item->duration_days }}h)</span>
                                                            @endif
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                onclick="openDateEditModal({{ $item->id }})"
                                                                class="text-[10px] text-gray-400 hover:text-indigo-600 transition inline-flex items-center gap-0.5"
                                                                title="Tambahkan tanggal mulai & selesai untuk Gantt Chart">
                                                            <span>📅 + Jadwal</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Qty -->
                                    <td class="py-3 px-3 text-center font-semibold text-gray-700 align-middle" id="qty-item-{{ $item->id }}">
                                        {{ $item->quantity }}
                                    </td>

                                    <!-- Harga Satuan -->
                                    <td class="py-3 px-4 text-right text-gray-600 align-middle" id="price-item-{{ $item->id }}">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        @if($isTarget)
                                            <div class="text-[9px] font-semibold text-amber-700" title="Harga induk dipakai sebagai target/plafon, bukan biaya tambahan">
                                                Target / Plafon
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Total Subtotal (Rollup jika Parent) -->
                                    <td class="py-3 px-4 text-right align-middle" id="subtotal-item-{{ $item->id }}">
                                        <div class="font-bold {{ $hasChildren ? 'text-indigo-700 text-sm' : 'text-gray-900' }}">
                                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                                        </div>
                                        @if($isTarget)
                                            <div class="text-[10px] text-gray-500">
                                                Terkumpul Rp {{ number_format($item->effective_completed_subtotal, 0, ',', '.') }}
                                                dari Rp {{ number_format($subtotal, 0, ',', '.') }}
                                            </div>
                                            @if($overBudget > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="Total rincian melebihi target">
                                                    Over budget Rp {{ number_format($overBudget, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        @elseif($hasChildren)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-200">
                                                Rollup Sub-item
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Catatan -->
                                    <td class="py-3 px-4 text-gray-500 text-xs align-middle" id="notes-item-{{ $item->id }}">
                                        @if($item->notes)
                                            <span class="line-clamp-1" title="{{ $item->notes }}">{{ $item->notes }}</span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>

                                    <!-- Aksi Hierarki & Edit -->
                                    <td class="py-3 px-4 text-center align-middle whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1">
                                            <!-- 1. Tombol + Sub (Tambah Anak di Bawah Baris Ini) -->
                                            <button type="button"
                                                    onclick="openInlineSubRow({{ $item->id }}, '{{ addslashes($item->title) }}', {{ $depth }})"
                                                    class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded text-[11px] transition shadow-2xs hover:scale-105 active:scale-95"
                                                    title="Tambah Sub-Item / Anak langsung di bawah baris ini">
                                                + Sub
                                            </button>

                                            <!-- 2. Tombol Indent (Jadikan Anak dari Baris Sebelumnya) -->
                                            @if($prevItem && $prevItem->id !== $item->parent_id && $item->parent_id !== $prevItem->id)
                                                <button type="button"
                                                        onclick="reparentItem({{ $item->id }}, {{ $prevItem->id }})"
                                                        class="px-1.5 py-1 text-slate-500 hover:text-indigo-700 hover:bg-indigo-50 rounded text-[11px] font-semibold transition"
                                                        title="Jadikan Sub dari item di atasnya (Indent ↳)">
                                                    ↳ Sub
                                                </button>
                                            @endif

                                            <!-- 3. Tombol Outdent (Naikkan Tingkat ke Atas / Jadikan Utama) -->
                                            @if($item->parent_id)
                                                @php
                                                    $parentOfParent = $item->parent ? $item->parent->parent_id : null;
                                                @endphp
                                                <button type="button"
                                                        onclick="reparentItem({{ $item->id }}, {{ $parentOfParent ? $parentOfParent : 'null' }})"
                                                        class="px-1.5 py-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded text-[11px] font-semibold transition"
                                                        title="Naikkan Tingkat / Jadikan Utama (Outdent ↰)">
                                                    ↰ Utama
                                                </button>
                                            @endif

                                            <!-- 4. Tombol Atur Tanggal Gantt Chart -->
                                            <button type="button"
                                                    onclick="openDateEditModal({{ $item->id }})"
                                                    class="p-1 text-gray-400 hover:text-indigo-600 rounded hover:bg-gray-100 transition"
                                                    title="Atur Tanggal Mulai & Selesai (Gantt Chart)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>

                                            <!-- 5. Tombol Edit Baris Inline -->
                                            <button type="button"
                                                    onclick="enableInlineEditRow({{ $item->id }}, '{{ addslashes($item->title) }}', {{ $item->quantity }}, {{ (float)$item->unit_price }}, '{{ addslashes($item->notes ?? '') }}', {{ $depth }}, '{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}', '{{ $item->end_date ? $item->end_date->format('Y-m-d') : '' }}')"
                                                    class="p-1 text-gray-400 hover:text-indigo-600 rounded hover:bg-gray-100 transition"
                                                    title="Edit Baris Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>

                                            <!-- 6. Tombol Hapus Baris -->
                                            <form action="{{ route('future-planner.items.destroy', [$futurePlan, $item]) }}" method="POST" onsubmit="return confirm('Hapus item &quot;{{ $item->title }}&quot; beserta sub-item di bawahnya?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-gray-400 hover:text-rose-600 rounded hover:bg-rose-50 transition" title="Hapus Item">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- ================================================================= -->
                            <!-- BARIS PERMANEN: TAMBAH ITEM (DENGAN PILIHAN PARENT / INDUK)       -->
                            <!-- ================================================================= -->
                            <tr id="quickAddRootRow" class="bg-indigo-50/40 hover:bg-indigo-50/60 border-t-2 border-dashed border-indigo-200 transition">
                                <td class="py-3 px-3 text-center align-middle">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs shadow-2xs">
                                        +
                                    </span>
                                </td>
                                <td class="py-3 px-4 align-middle">
                                    <div class="flex items-center gap-2">
                                        <!-- Selector Pilihan Induk Langsung di Baris -->
                                        <div class="shrink-0 w-36">
                                            <select id="quick_parent_id" onchange="onQuickParentChange()"
                                                    class="w-full text-[11px] bg-white border border-indigo-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 font-medium text-gray-700 shadow-2xs"
                                                    title="Pilih apakah ini item utama atau anak (sub) dari item lain">
                                                <option value="">📁 Utama (L1)</option>
                                                @foreach($availableParents as $parent)
                                                    <option value="{{ $parent->id }}">↳ Sub: {{ Str::limit($parent->title, 18) }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Badge Tingkat -->
                                        <span id="quick_level_badge" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 shrink-0">
                                            L1
                                        </span>

                                        <!-- Input Nama Item -->
                                        <input type="text"
                                               id="quick_title"
                                               placeholder="+ Ketik nama item baru... (Tekan Enter untuk simpan)"
                                               class="w-full text-xs bg-white border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                               onkeydown="handleQuickAddKey(event)">
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center align-middle">
                                    <input type="number"
                                           id="quick_qty"
                                           value="1"
                                           min="1"
                                           class="w-16 text-center text-xs bg-white border border-gray-300 rounded-lg py-1.5 focus:ring-2 focus:ring-indigo-500 shadow-sm"
                                           oninput="updateQuickTotalPreview()"
                                           onkeydown="handleQuickAddKey(event)">
                                </td>
                                <td class="py-3 px-4 text-right align-middle">
                                    <input type="number"
                                           id="quick_price"
                                           placeholder="0"
                                           min="0"
                                           step="1000"
                                           class="w-32 text-right text-xs bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 shadow-sm"
                                           oninput="updateQuickTotalPreview()"
                                           onkeydown="handleQuickAddKey(event)">
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-indigo-700 align-middle">
                                    <span id="quick_total_preview">Rp 0</span>
                                </td>
                                <td class="py-3 px-4 align-middle">
                                    <input type="text"
                                           id="quick_notes"
                                           placeholder="Catatan / link toko..."
                                           class="w-full text-xs bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 shadow-sm"
                                           onkeydown="handleQuickAddKey(event)">
                                    <div class="flex items-center gap-1 mt-1 text-[10px] text-gray-500">
                                        <span title="Jadwal Linimasa">📅</span>
                                        <input type="date" id="quick_start_date" class="text-[10px] py-0.5 px-1 bg-white border border-gray-200 rounded text-gray-600 focus:ring-1 focus:ring-indigo-500" title="Tanggal Mulai">
                                        <span>s/d</span>
                                        <input type="date" id="quick_end_date" class="text-[10px] py-0.5 px-1 bg-white border border-gray-200 rounded text-gray-600 focus:ring-1 focus:ring-indigo-500" title="Tanggal Selesai">
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center align-middle">
                                    <button type="button"
                                            id="btnQuickSubmit"
                                            onclick="submitQuickAdd()"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-xs shadow-sm transition hover:scale-105 active:scale-95 disabled:opacity-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span>Tambah</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- FOOTER: TOTAL KESELURUHAN DARI SELURUH ITEM FUTURE -->
                        <tfoot>
                            <tr class="bg-gray-900 text-white font-bold border-t-2 border-indigo-500">
                                <td class="py-4 px-3 text-center align-middle">
                                    <span class="text-indigo-400 text-sm">∑</span>
                                </td>
                                <td class="py-4 px-4 align-middle">
                                    <div class="text-sm tracking-wide uppercase">TOTAL KESELURUHAN ITEM (GRAND TOTAL)</div>
                                    <div class="text-[11px] font-normal text-gray-300 mt-0.5" id="footer_summary_info">
                                        ✅ Terbeli: Rp {{ number_format($totalCompleted, 0, ',', '.') }} | ⏳ Sisa: Rp {{ number_format($totalRemaining, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="py-4 px-3 text-center text-sm font-semibold text-gray-200 align-middle" id="footer_total_quantity">
                                    {{ number_format($totalQuantity) }}
                                </td>
                                <td class="py-4 px-4 text-right text-xs text-gray-400 align-middle" id="footer_total_items_count">
                                    {{ $totalItemsCount }} item
                                </td>
                                <td class="py-4 px-4 text-right text-base font-extrabold text-indigo-300 align-middle" id="footer_grand_total">
                                    Rp {{ number_format($totalEstimated, 0, ',', '.') }}
                                </td>
                                <td colspan="2" class="py-4 px-4 text-center text-xs text-gray-400 align-middle">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-700" id="footer_progress_badge">
                                        {{ $progressPercentage }}% Terpenuhi
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </div> <!-- /tableViewContainer -->

            <!-- WRAPPER MODE 2: GANTT CHART (LINIMASA) -->
            <div id="ganttViewContainer" class="hidden space-y-4">
                <!-- Toolbar Gantt Chart -->
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider mr-1">Skala Waktu:</span>
                        <div class="inline-flex rounded-xl bg-gray-100 p-0.5 text-xs font-semibold">
                            <button type="button"
                                    onclick="setGanttScale('day')"
                                    id="btnScaleDay"
                                    class="px-3 py-1 rounded-lg transition bg-white text-indigo-700 shadow-2xs">
                                Hari
                            </button>
                            <button type="button"
                                    onclick="setGanttScale('week')"
                                    id="btnScaleWeek"
                                    class="px-3 py-1 rounded-lg transition text-gray-600 hover:text-gray-900">
                                Minggu
                            </button>
                            <button type="button"
                                    onclick="setGanttScale('month')"
                                    id="btnScaleMonth"
                                    class="px-3 py-1 rounded-lg transition text-gray-600 hover:text-gray-900">
                                Bulan
                            </button>
                        </div>

                        <button type="button"
                                onclick="scrollToToday()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl border border-rose-200 transition"
                                title="Gulir ke penanda tanggal hari ini">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Hari Ini</span>
                        </button>
                    </div>

                    <!-- Legend Status Linimasa -->
                    <div class="flex items-center gap-2 flex-wrap text-[11px]">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Selesai
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 font-medium">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Sedang Berjalan
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Akan Datang
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 font-medium">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Terlambat
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-dashed border-amber-300 font-medium">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Belum Ada Tanggal
                        </span>
                    </div>
                </div>

                <!-- Info Ringkasan Timeline -->
                <div class="bg-gradient-to-r from-indigo-50/80 via-blue-50/60 to-purple-50/60 border border-indigo-100 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                            📊
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <span>Linimasa Jadwal Rencana</span>
                            </div>
                            <div class="text-gray-500 text-[11px] mt-0.5" id="ganttDateRangeSummary">
                                Memuat rentang waktu rencana...
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-white border border-indigo-200 text-indigo-800" id="ganttScheduledCountSummary">
                            0 item terjadwal
                        </span>
                    </div>
                </div>

                <!-- GANTT DUAL-PANE CANVAS -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col" id="ganttMainWrapper">
                    <div class="flex border-b border-gray-200 bg-gray-50/90 text-xs font-bold text-gray-700 select-none">
                        <!-- Left Pane Header -->
                        <div class="w-80 sm:w-96 shrink-0 border-r border-gray-200 px-4 py-3 flex items-center justify-between">
                            <span>Daftar Item & Hierarki</span>
                            <span class="text-[11px] text-gray-400 font-normal">Jadwal</span>
                        </div>
                        <!-- Right Pane Timeline Header Scroll Container -->
                        <div class="flex-1 overflow-x-hidden" id="ganttHeaderScroll">
                            <div id="ganttTimelineHeader" class="relative">
                                <!-- Generated via JS: Months Row & Days/Weeks Row -->
                            </div>
                        </div>
                    </div>

                    <!-- Split Body -->
                    <div class="flex overflow-hidden relative min-h-[350px]">
                        <!-- Left Pane: Tasks List (Y-scrollable synced) -->
                        <div class="w-80 sm:w-96 shrink-0 border-r border-gray-200 bg-white overflow-hidden divide-y divide-gray-100" id="ganttLeftPane">
                            <!-- Generated via JS: Item rows matching timeline rows -->
                        </div>

                        <!-- Right Pane: Timeline Grid & Bars (Horizontally and vertically scrollable) -->
                        <div class="flex-1 overflow-auto bg-white relative" id="ganttRightPane" onscroll="syncGanttScroll(this)">
                            <div id="ganttGridCanvas" class="relative min-w-full">
                                <!-- Background columns & Today line -->
                                <div id="ganttGridBackground" class="absolute inset-0 pointer-events-none"></div>
                                <div id="ganttTodayMarker" class="absolute top-0 bottom-0 z-20 pointer-events-none border-l-2 border-rose-500 border-dashed hidden">
                                    <span class="absolute top-1 -left-7 px-1.5 py-0.5 rounded bg-rose-600 text-white text-[9px] font-bold shadow-sm whitespace-nowrap">Hari Ini</span>
                                </div>
                                <!-- Task Bars Container -->
                                <div id="ganttBarsContainer" class="relative z-10 divide-y divide-gray-100/70">
                                    <!-- Generated via JS: Horizontal rows with bars -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL KOLABORASI, KODE UNIK & KELOLA ANGGOTA -->
    <div id="collaborationModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" onclick="closeCollaborationModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle">
                <!-- Header Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            👥
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Kolaborasi & Anggota Rencana</h3>
                            <p class="text-xs text-gray-500">Kelola anggota yang dapat melihat dan mengisi rencana ini bersama.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCollaborationModal()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-6">
                    <!-- SECTION 1: KODE UNIK RENCANA -->
                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🔑</span>
                                <span>Kode Unik Rencana</span>
                            </label>
                            <span class="text-[11px] text-indigo-600 font-medium">Bisa dibagikan ke teman</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-white border border-indigo-200 rounded-xl px-3.5 py-2 font-mono font-bold text-lg text-indigo-700 tracking-wider flex items-center justify-between shadow-2xs">
                                <span id="shareCodeText">{{ $futurePlan->share_code }}</span>
                                <span class="text-[10px] uppercase font-sans font-semibold bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Kode Unik</span>
                            </div>
                            <button type="button"
                                    id="btnCopyCode"
                                    onclick="copyShareCode('{{ $futurePlan->share_code }}')"
                                    class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-2xs transition hover:shadow flex items-center gap-1.5 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span id="btnCopyCodeText">Salin Kode</span>
                            </button>
                        </div>

                        <div class="mt-2.5 flex items-center justify-between text-xs text-indigo-900/80">
                            <span>Atau bagikan link langsung:</span>
                            <button type="button"
                                    id="btnCopyLink"
                                    onclick="copyShareLink('{{ route('future-planner.join.form', $futurePlan->share_code) }}')"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold underline inline-flex items-center gap-1">
                                🔗 Salin Link Undangan
                            </button>
                        </div>
                    </div>

                    <!-- SECTION 2: TAMBAH ANGGOTA LANGSUNG (KHUSUS PEMILIK) -->
                    @if($isOwner)
                        <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>➕</span>
                                <span>Undang Anggota Langsung</span>
                            </label>
                            <form action="{{ route('future-planner.members.store', $futurePlan) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <input type="email"
                                           name="email"
                                           required
                                           placeholder="Masukkan email pengguna (cth: teman@gmail.com)"
                                           class="flex-1 text-xs border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    <select name="role" class="text-xs border border-gray-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500">
                                        <option value="editor">Editor (Bisa kelola item)</option>
                                        <option value="viewer">Viewer (Hanya lihat)</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-2xs transition whitespace-nowrap">
                                        + Tambahkan
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-500">Pengguna harus sudah memiliki akun terdaftar di Finansia.</p>
                            </form>
                        </div>
                    @endif

                    <!-- SECTION 3: DAFTAR ANGGOTA RENCANA -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                <span>👥</span>
                                <span>Daftar Anggota ({{ $allMembers->count() }})</span>
                            </h4>
                            <span class="text-[11px] text-gray-400">Status Online diperbarui berkala</span>
                        </div>

                        <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto rounded-xl border border-gray-200">
                            @foreach($allMembers as $member)
                                @php
                                    $name = trim($member->name ?: 'User');
                                    $initial = strtoupper(mb_substr($name, 0, 1));
                                    $isThisMemberOwner = $futurePlan->isOwner($member);
                                    $isActive = $member->pivot->last_active_at && \Carbon\Carbon::parse($member->pivot->last_active_at)->gte(now()->subMinutes(3));
                                    $memberRole = $member->pivot->role ?? ($isThisMemberOwner ? 'owner' : 'editor');

                                    $colors = ['bg-indigo-600', 'bg-emerald-600', 'bg-violet-600', 'bg-rose-600', 'bg-sky-600', 'bg-amber-600', 'bg-teal-600', 'bg-fuchsia-600'];
                                    $colorClass = $colors[$member->id % count($colors)];
                                @endphp
                                <div class="p-3 flex items-center justify-between gap-3 hover:bg-gray-50/70 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Lingkaran Huruf Pertama -->
                                        <div class="relative shrink-0">
                                            <div class="w-9 h-9 rounded-full {{ $colorClass }} text-white font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-2xs">
                                                {{ $initial }}
                                            </div>
                                            @if($isActive)
                                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full" title="Sedang aktif"></span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="text-xs font-bold text-gray-800 truncate">{{ $name }}</p>
                                                @if(auth()->id() === $member->id)
                                                    <span class="text-[10px] font-semibold bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded">Anda</span>
                                                @endif
                                                @if($isThisMemberOwner)
                                                    <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-700 px-1.5 py-0.2 rounded-full">Pemilik</span>
                                                @else
                                                    <span class="text-[10px] font-medium bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full capitalize">{{ $memberRole }}</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-400">
                                                <span class="truncate">{{ $member->email }}</span>
                                                <span>•</span>
                                                @if($isActive)
                                                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                                        Sedang Aktif
                                                    </span>
                                                @else
                                                    <span>{{ $member->pivot->last_active_at ? \Carbon\Carbon::parse($member->pivot->last_active_at)->diffForHumans() : 'Belum aktif' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        @if($isOwner && !$isThisMemberOwner)
                                            <!-- Hapus Anggota (Hanya Pemilik) -->
                                            <form action="{{ route('future-planner.members.destroy', [$futurePlan, $member]) }}" method="POST" onsubmit="return confirm('Keluarkan anggota {{ $name }} dari rencana ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Keluarkan Anggota">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif(!$isOwner && auth()->id() === $member->id)
                                            <!-- Keluar dari Rencana (Anggota Mandiri) -->
                                            <form action="{{ route('future-planner.members.destroy', [$futurePlan, $member]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari rencana kolaborasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium px-2 py-1 rounded hover:bg-rose-50 transition">
                                                    Keluar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="closeCollaborationModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL IMPOR SPREADSHEET (EXCEL / CSV) -->
    <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-import-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" onclick="closeImportModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle">
                <!-- Header Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                            📥
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" id="modal-import-title">Impor Rincian Item Rencana</h3>
                            <p class="text-xs text-gray-500">Unggah berkas spreadsheet Excel (.xlsx) atau CSV untuk mengisi item kebutuhan secara cepat.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Template Download Card -->
                <div class="mt-4 bg-gradient-to-r from-blue-50/70 to-indigo-50/70 border border-blue-100 rounded-2xl p-4">
                    <div class="space-y-1">
                        <h4 class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                            <span>📋</span>
                            <span>Belum punya format file yang sesuai?</span>
                        </h4>
                        <p class="text-[11px] text-blue-800/80 leading-relaxed">
                            Unduh contoh format berkas untuk melihat contoh susunan kolom, penomoran kode hirarki bertingkat (contoh: <code>1</code>, <code>1.1</code>, <code>1.1.1</code>), dan relasi nama induk.
                        </p>
                    </div>
                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('future-planner.export-template', ['format' => 'xlsx']) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-xl shadow-2xs transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span>Unduh Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('future-planner.export-template', ['format' => 'csv']) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs rounded-xl border border-gray-200 shadow-2xs transition">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unduh Template CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Form Upload -->
                <form action="{{ route('future-planner.import', $futurePlan) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="importForm"
                      onsubmit="handleImportSubmit(event)"
                      class="mt-5 space-y-4">
                    @csrf

                    <!-- Area File Drag & Drop -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih Berkas Spreadsheet <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-gray-300 hover:border-indigo-400 rounded-2xl p-5 text-center transition cursor-pointer bg-gray-50/50 hover:bg-indigo-50/30"
                             id="dropZone"
                             onclick="document.getElementById('importFileInput').click()">
                            <input type="file"
                                   name="file"
                                   id="importFileInput"
                                   accept=".xlsx,.xls,.csv,.txt"
                                   required
                                   class="hidden"
                                   onchange="updateSelectedFileName(this)">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-2xs">
                                    📊
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-xs font-semibold text-gray-800" id="fileUploadPrompt">
                                        Klik untuk memilih berkas atau seret ke sini
                                    </p>
                                    <p class="text-[11px] text-gray-400">Mendukung format .xlsx, .xls, .csv (Maks. 10 MB)</p>
                                </div>
                                <div id="fileSelectedBadge" class="hidden mt-2 inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl">
                                    <span>📄 <span id="selectedFileNameText"></span></span>
                                    <span class="text-[10px] text-emerald-600 font-mono" id="selectedFileSizeText"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Mode Impor: Append vs Replace -->
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Mode Impor
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-2 rounded-xl hover:bg-white transition cursor-pointer border border-transparent hover:border-gray-200">
                                <input type="radio" name="mode" value="append" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-800 block">Tambah ke data yang ada (Append)</span>
                                    <span class="text-[11px] text-gray-500 block leading-normal">
                                        Data dari file akan ditambahkan ke item saat ini. Jika nama induk sesuai dengan item yang sudah ada, sub-item akan otomatis dihubungkan.
                                    </span>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-2 rounded-xl hover:bg-white transition cursor-pointer border border-transparent hover:border-gray-200">
                                <input type="radio" name="mode" value="replace" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                <div>
                                    <span class="text-xs font-bold text-rose-700 block">Timpa seluruh data yang ada (Replace)</span>
                                    <span class="text-[11px] text-gray-500 block leading-normal">
                                        Semua item rincian rencana saat ini akan dihapus dan digantikan seluruhnya dengan data baru dari berkas spreadsheet.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                        <button type="button"
                                onclick="closeImportModal()"
                                class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                id="btnSubmitImport"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm transition hover:shadow">
                            <span id="btnSubmitImportText">Mulai Impor Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ATUR TANGGAL LINIMASA / GANTT CHART -->
    <div id="dateEditModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-date-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" onclick="closeDateEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle">
                <!-- Header Modal -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            📅
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900" id="modal-date-title">Atur Jadwal Linimasa</h3>
                            <p class="text-xs text-gray-500 line-clamp-1" id="dateModalItemTitle">Nama Item</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDateEditModal()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form Atur Tanggal -->
                <form id="dateEditForm" onsubmit="handleDateFormSubmit(event)" class="mt-4 space-y-4">
                    <input type="hidden" id="dateModalItemId">

                    <!-- Preset Cepat -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                            Pilihan Rentang Cepat
                        </label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="applyDatePreset('today')" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition font-medium">
                                Hari Ini (1 Hari)
                            </button>
                            <button type="button" onclick="applyDatePreset('1week')" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition font-medium">
                                1 Minggu
                            </button>
                            <button type="button" onclick="applyDatePreset('2weeks')" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition font-medium">
                                2 Minggu
                            </button>
                            <button type="button" onclick="applyDatePreset('1month')" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition font-medium">
                                1 Bulan
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Mulai
                            </label>
                            <input type="date"
                                   id="dateModalStartDate"
                                   class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                   onchange="updateDateModalDurationPreview()">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Selesai
                            </label>
                            <input type="date"
                                   id="dateModalEndDate"
                                   class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                   onchange="updateDateModalDurationPreview()">
                        </div>
                    </div>

                    <!-- Estimasi Durasi -->
                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl px-3.5 py-2 flex items-center justify-between text-xs">
                        <span class="text-indigo-900 font-medium">Total Durasi Linimasa:</span>
                        <span class="font-bold text-indigo-700" id="dateModalDurationText">-</span>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <button type="button"
                                onclick="clearItemDates()"
                                class="text-xs text-rose-600 hover:text-rose-800 font-medium hover:underline">
                            Hapus Jadwal
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    onclick="closeDateEditModal()"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    id="btnSaveDateModal"
                                    class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                                Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $ganttItemsData = $items->map(function ($item) {
            return [
                'id'                   => $item->id,
                'parent_id'            => $item->parent_id,
                'title'                => $item->title,
                'quantity'             => (int) $item->quantity,
                'unit_price'           => (float) $item->unit_price,
                'subtotal'             => (float) $item->effective_subtotal,
                'is_completed'         => (bool) $item->is_completed,
                'notes'                => $item->notes ?? '',
                'depth'                => (int) ($item->depth ?? 0),
                'has_children'         => $item->hasChildren(),
                'start_date'           => $item->start_date ? $item->start_date->format('Y-m-d') : null,
                'end_date'             => $item->end_date ? $item->end_date->format('Y-m-d') : null,
                'effective_start_date' => $item->effective_start_date,
                'effective_end_date'   => $item->effective_end_date,
                'duration_days'        => $item->duration_days,
                'timeline_status'      => $item->timeline_status,
            ];
        });
    @endphp

    <!-- Script JavaScript untuk Interaktivitas Spreadsheet & Parent-Child Navigation -->
    <script>
        const PLAN_ID = {{ $futurePlan->id }};
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let GANTT_ITEMS = @json($ganttItemsData);
        let currentGanttScale = 'day';
        let currentViewMode = localStorage.getItem('futurePlanner_viewMode_' + PLAN_ID) || 'table';

        // -------------------------------------------------------------------------
        // 1. PENGATURAN FULL WIDTH MODE
        // -------------------------------------------------------------------------
        function initFullWidthMode() {
            const savedMode = localStorage.getItem('futurePlanner_fullWidth');
            // Default: Full Width Mode aktif
            if (savedMode === 'boxed') {
                applyWidthMode(false);
            } else {
                applyWidthMode(true);
            }
        }

        function toggleFullWidthMode() {
            const container = document.getElementById('plannerContainer');
            const isFull = !container.classList.contains('max-w-7xl');
            applyWidthMode(!isFull);
            localStorage.setItem('futurePlanner_fullWidth', isFull ? 'boxed' : 'full');
        }

        function applyWidthMode(isFull) {
            const container = document.getElementById('plannerContainer');
            const label = document.getElementById('labelToggleWidth');

            if (isFull) {
                container.classList.remove('max-w-7xl', 'mx-auto');
                container.classList.add('w-full');
                if (label) label.textContent = 'Mode Boxed';
            } else {
                container.classList.remove('w-full');
                container.classList.add('max-w-7xl', 'mx-auto');
                if (label) label.textContent = 'Mode Full Width';
            }
        }

        document.addEventListener('DOMContentLoaded', initFullWidthMode);

        // Format angka ke Rupiah
        function formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }

        // Focus kursor ke baris tambah item cepat
        function focusQuickAddRow() {
            const input = document.getElementById('quick_title');
            if (input) {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                input.focus();
            }
        }

        // Saat pilihan induk pada quick add row diubah
        function onQuickParentChange() {
            const parentSelect = document.getElementById('quick_parent_id');
            const levelBadge = document.getElementById('quick_level_badge');
            const titleInput = document.getElementById('quick_title');

            if (parentSelect.value) {
                levelBadge.textContent = 'Sub';
                levelBadge.className = 'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 shrink-0';
                titleInput.placeholder = '+ Ketik nama sub-item... (Tekan Enter untuk simpan)';
            } else {
                levelBadge.textContent = 'L1';
                levelBadge.className = 'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 shrink-0';
                titleInput.placeholder = '+ Ketik nama item baru... (Tekan Enter untuk simpan)';
            }
        }

        // Kalkulasi live preview total pada baris Quick-Add Root
        function updateQuickTotalPreview() {
            const qty = parseFloat(document.getElementById('quick_qty').value) || 1;
            const price = parseFloat(document.getElementById('quick_price').value) || 0;
            const subtotal = qty * price;
            document.getElementById('quick_total_preview').textContent = formatRupiah(subtotal);
        }

        function handleQuickAddKey(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitQuickAdd();
            }
        }

        // Kirim Quick-Add Baris
        async function submitQuickAdd() {
            const parentSelect = document.getElementById('quick_parent_id');
            const titleInput = document.getElementById('quick_title');
            const qtyInput = document.getElementById('quick_qty');
            const priceInput = document.getElementById('quick_price');
            const notesInput = document.getElementById('quick_notes');
            const btnSubmit = document.getElementById('btnQuickSubmit');

            const title = titleInput.value.trim();
            if (!title) {
                titleInput.focus();
                titleInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-200');
                return;
            }
            titleInput.classList.remove('border-rose-500', 'ring-2', 'ring-rose-200');

            const parentId = parentSelect.value ? parseInt(parentSelect.value) : null;
            const qty = parseInt(qtyInput.value) || 1;
            const price = parseFloat(priceInput.value) || 0;
            const notes = notesInput.value.trim();
            const startDate = document.getElementById('quick_start_date') ? document.getElementById('quick_start_date').value : null;
            const endDate = document.getElementById('quick_end_date') ? document.getElementById('quick_end_date').value : null;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menyimpan...</span>
            `;

            try {
                const response = await fetch(`/future-planner/${PLAN_ID}/items`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        quantity: qty,
                        unit_price: price,
                        notes: notes,
                        parent_id: parentId,
                        start_date: startDate || null,
                        end_date: endDate || null
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.message || 'Gagal menyimpan item.');
                }

                // Sukses: reload halaman secara mulus agar seluruh tree & rollup dihitung 100% akurat
                window.location.reload();

            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat menyimpan item.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah</span>
                `;
            }
        }

        // -------------------------------------------------------------------------
        // 2. REPARENT / INDENT / OUTDENT ITEM CEPAT (1-KLIK GANTI PARENT-CHILD)
        // -------------------------------------------------------------------------
        async function reparentItem(itemId, newParentId) {
            try {
                const response = await fetch(`/future-planner/${PLAN_ID}/items/${itemId}/reparent`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        parent_id: newParentId
                    })
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal mengubah hierarki item.');
                }

                // Reload untuk menyusun ulang pohon dan rollup kalkulasi
                window.location.reload();

            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat mengubah hierarki cabang.');
            }
        }

        // -------------------------------------------------------------------------
        // 3. INLINE SUB-ITEM ROW (TAMBAH SUB-TASK LANGSUNG DI BAWAH INDUK)
        // -------------------------------------------------------------------------
        function openInlineSubRow(parentId, parentTitle, parentDepth) {
            const existingSubRow = document.getElementById('active-inline-sub-row');
            if (existingSubRow) {
                existingSubRow.remove();
            }

            const parentRow = document.getElementById(`row-item-${parentId}`);
            if (!parentRow) return;

            const childDepth = parentDepth + 1;
            const indentPx = childDepth * 28;

            const subRowHtml = `
                <tr id="active-inline-sub-row" class="bg-amber-50/70 border-l-4 border-amber-400 border-y border-amber-200 transition">
                    <td class="py-2.5 px-3 text-center align-middle">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-200 text-amber-900 text-[10px] font-bold">
                            ↳
                        </span>
                    </td>
                    <td class="py-2.5 px-4 align-middle">
                        <div class="flex items-center gap-1.5" style="padding-left: ${indentPx}px;">
                            <span class="text-amber-500 font-mono text-sm leading-none select-none">└──</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                Sub dari "${parentTitle}"
                            </span>
                            <input type="text"
                                   id="sub_title_${parentId}"
                                   placeholder="Ketik nama sub-item... (Tekan Enter untuk simpan)"
                                   class="w-full text-xs bg-white border border-amber-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-sm font-medium"
                                   onkeydown="handleSubAddKey(event, ${parentId})">
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-center align-middle">
                        <input type="number"
                               id="sub_qty_${parentId}"
                               value="1"
                               min="1"
                               class="w-16 text-center text-xs bg-white border border-amber-300 rounded-lg py-1.5 focus:ring-2 focus:ring-amber-500 shadow-sm"
                               oninput="updateSubTotalPreview(${parentId})"
                               onkeydown="handleSubAddKey(event, ${parentId})">
                    </td>
                    <td class="py-2.5 px-4 text-right align-middle">
                        <input type="number"
                               id="sub_price_${parentId}"
                               placeholder="0"
                               min="0"
                               step="1000"
                               class="w-32 text-right text-xs bg-white border border-amber-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-amber-500 shadow-sm"
                               oninput="updateSubTotalPreview(${parentId})"
                               onkeydown="handleSubAddKey(event, ${parentId})">
                    </td>
                    <td class="py-2.5 px-4 text-right font-bold text-amber-800 align-middle">
                        <span id="sub_total_preview_${parentId}">Rp 0</span>
                    </td>
                    <td class="py-2.5 px-4 align-middle">
                        <input type="text"
                               id="sub_notes_${parentId}"
                               placeholder="Catatan sub-item..."
                               class="w-full text-xs bg-white border border-amber-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-amber-500 shadow-sm"
                               onkeydown="handleSubAddKey(event, ${parentId})">
                    </td>
                    <td class="py-2.5 px-4 text-center align-middle whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button"
                                    id="btnSubSubmit_${parentId}"
                                    onclick="submitSubAdd(${parentId})"
                                    class="px-2.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-xs shadow-sm transition hover:scale-105 active:scale-95">
                                Simpan
                            </button>
                            <button type="button"
                                    onclick="cancelInlineSubRow()"
                                    class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                    title="Batal (Esc)">
                                ✕
                            </button>
                        </div>
                    </td>
                </tr>
            `;

            parentRow.insertAdjacentHTML('afterend', subRowHtml);

            const subInput = document.getElementById(`sub_title_${parentId}`);
            if (subInput) {
                subInput.focus();
            }
        }

        function cancelInlineSubRow() {
            const existingSubRow = document.getElementById('active-inline-sub-row');
            if (existingSubRow) {
                existingSubRow.remove();
            }
        }

        function updateSubTotalPreview(parentId) {
            const qty = parseFloat(document.getElementById(`sub_qty_${parentId}`).value) || 1;
            const price = parseFloat(document.getElementById(`sub_price_${parentId}`).value) || 0;
            const subtotal = qty * price;
            document.getElementById(`sub_total_preview_${parentId}`).textContent = formatRupiah(subtotal);
        }

        function handleSubAddKey(event, parentId) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitSubAdd(parentId);
            } else if (event.key === 'Escape') {
                cancelInlineSubRow();
            }
        }

        async function submitSubAdd(parentId) {
            const titleInput = document.getElementById(`sub_title_${parentId}`);
            const qtyInput = document.getElementById(`sub_qty_${parentId}`);
            const priceInput = document.getElementById(`sub_price_${parentId}`);
            const notesInput = document.getElementById(`sub_notes_${parentId}`);
            const btnSubmit = document.getElementById(`btnSubSubmit_${parentId}`);

            const title = titleInput.value.trim();
            if (!title) {
                titleInput.focus();
                titleInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-200');
                return;
            }

            const qty = parseInt(qtyInput.value) || 1;
            const price = parseFloat(priceInput.value) || 0;
            const notes = notesInput.value.trim();

            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Menyimpan...';

            try {
                const response = await fetch(`/future-planner/${PLAN_ID}/items`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        quantity: qty,
                        unit_price: price,
                        notes: notes,
                        parent_id: parentId
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.message || 'Gagal menambahkan sub-item.');
                }

                window.location.reload();

            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat menyimpan sub-item.');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Simpan';
            }
        }

        // -------------------------------------------------------------------------
        // 4. INLINE EDIT BARIS
        // -------------------------------------------------------------------------
        let currentEditingRow = null;

        function enableInlineEditRow(itemId, currentTitle, currentQty, currentPrice, currentNotes, depth, currentStartDate = '', currentEndDate = '') {
            const rowEl = document.getElementById(`row-item-${itemId}`);
            if (!rowEl) return;

            if (currentEditingRow && currentEditingRow.id !== itemId) {
                cancelInlineEditRow(currentEditingRow.id);
            }

            currentEditingRow = {
                id: itemId,
                originalHtml: rowEl.innerHTML
            };

            const indentPx = depth * 28;

            rowEl.classList.add('bg-blue-50/70', 'border-y', 'border-blue-300');
            rowEl.innerHTML = `
                <td class="py-2 px-3 text-center align-middle">
                    <span class="text-blue-500 font-bold">✎</span>
                </td>
                <td class="py-2 px-4 align-middle">
                    <div class="flex items-center gap-1.5" style="padding-left: ${indentPx}px;">
                        ${depth > 0 ? '<span class="text-indigo-400 font-mono text-xs select-none">└──</span>' : ''}
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-900">
                            L${depth + 1}
                        </span>
                        <input type="text"
                               id="edit_inline_title_${itemId}"
                               value="${currentTitle}"
                               class="w-full text-xs bg-white border border-blue-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 font-medium"
                               onkeydown="handleEditRowKey(event, ${itemId})">
                    </div>
                </td>
                <td class="py-2 px-3 text-center align-middle">
                    <input type="number"
                           id="edit_inline_qty_${itemId}"
                           value="${currentQty}"
                           min="1"
                           class="w-16 text-center text-xs bg-white border border-blue-300 rounded-lg py-1.5 focus:ring-2 focus:ring-blue-500"
                           oninput="updateEditTotalPreview(${itemId})"
                           onkeydown="handleEditRowKey(event, ${itemId})">
                </td>
                <td class="py-2 px-4 text-right align-middle">
                    <input type="number"
                           id="edit_inline_price_${itemId}"
                           value="${currentPrice}"
                           min="0"
                           step="1000"
                           class="w-32 text-right text-xs bg-white border border-blue-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500"
                           oninput="updateEditTotalPreview(${itemId})"
                           onkeydown="handleEditRowKey(event, ${itemId})">
                </td>
                <td class="py-2 px-4 text-right font-bold text-blue-900 align-middle">
                    <span id="edit_inline_total_${itemId}">
                        ${formatRupiah(currentQty * currentPrice)}
                    </span>
                </td>
                <td class="py-2 px-4 align-middle">
                    <input type="text"
                           id="edit_inline_notes_${itemId}"
                           value="${currentNotes}"
                           placeholder="Catatan..."
                           class="w-full text-xs bg-white border border-blue-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500"
                           onkeydown="handleEditRowKey(event, ${itemId})">
                    <div class="flex items-center gap-1 mt-1 text-[10px] text-gray-500">
                        <span title="Jadwal Linimasa">📅</span>
                        <input type="date" id="edit_inline_start_${itemId}" value="${currentStartDate || ''}" class="text-[10px] py-0.5 px-1 bg-white border border-blue-200 rounded text-gray-700" title="Tanggal Mulai">
                        <span>s/d</span>
                        <input type="date" id="edit_inline_end_${itemId}" value="${currentEndDate || ''}" class="text-[10px] py-0.5 px-1 bg-white border border-blue-200 rounded text-gray-700" title="Tanggal Selesai">
                    </div>
                </td>
                <td class="py-2 px-4 text-center align-middle whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button"
                                id="btnSaveEdit_${itemId}"
                                onclick="saveInlineEditRow(${itemId})"
                                class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs shadow-sm transition">
                            Simpan
                        </button>
                        <button type="button"
                                onclick="cancelInlineEditRow(${itemId})"
                                class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                title="Batal (Esc)">
                            ✕
                        </button>
                    </div>
                </td>
            `;

            const input = document.getElementById(`edit_inline_title_${itemId}`);
            if (input) {
                input.focus();
                input.select();
            }
        }

        function cancelInlineEditRow(itemId) {
            const rowEl = document.getElementById(`row-item-${itemId}`);
            if (rowEl && currentEditingRow && currentEditingRow.id === itemId) {
                rowEl.innerHTML = currentEditingRow.originalHtml;
                rowEl.classList.remove('bg-blue-50/70', 'border-y', 'border-blue-300');
                currentEditingRow = null;
            }
        }

        function updateEditTotalPreview(itemId) {
            const qty = parseFloat(document.getElementById(`edit_inline_qty_${itemId}`).value) || 1;
            const price = parseFloat(document.getElementById(`edit_inline_price_${itemId}`).value) || 0;
            document.getElementById(`edit_inline_total_${itemId}`).textContent = formatRupiah(qty * price);
        }

        function handleEditRowKey(event, itemId) {
            if (event.key === 'Enter') {
                event.preventDefault();
                saveInlineEditRow(itemId);
            } else if (event.key === 'Escape') {
                cancelInlineEditRow(itemId);
            }
        }

        async function saveInlineEditRow(itemId) {
            const titleInput = document.getElementById(`edit_inline_title_${itemId}`);
            const qtyInput = document.getElementById(`edit_inline_qty_${itemId}`);
            const priceInput = document.getElementById(`edit_inline_price_${itemId}`);
            const notesInput = document.getElementById(`edit_inline_notes_${itemId}`);
            const btnSubmit = document.getElementById(`btnSaveEdit_${itemId}`);

            const title = titleInput.value.trim();
            if (!title) {
                titleInput.focus();
                return;
            }

            const qty = parseInt(qtyInput.value) || 1;
            const price = parseFloat(priceInput.value) || 0;
            const notes = notesInput.value.trim();
            const startInput = document.getElementById(`edit_inline_start_${itemId}`);
            const endInput = document.getElementById(`edit_inline_end_${itemId}`);
            const startDate = startInput ? startInput.value : null;
            const endDate = endInput ? endInput.value : null;

            btnSubmit.disabled = true;
            btnSubmit.textContent = '...';

            try {
                const response = await fetch(`/future-planner/${PLAN_ID}/items/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        quantity: qty,
                        unit_price: price,
                        notes: notes,
                        start_date: startDate || null,
                        end_date: endDate || null
                    })
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal menyimpan perubahan.');
                }

                window.location.reload();

            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat mengupdate item.');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Simpan';
            }
        }

        // -------------------------------------------------------------------------
        // 5. AJAX CHECKLIST TOGGLE
        // -------------------------------------------------------------------------
        async function toggleItemStatus(itemId, checkboxEl) {
            const titleEl = document.getElementById(`title-item-${itemId}`);
            const rowEl = document.getElementById(`row-item-${itemId}`);

            try {
                const response = await fetch(`/future-planner/${PLAN_ID}/items/${itemId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Gagal memperbarui status item');
                }

                const data = await response.json();

                if (data.is_completed) {
                    titleEl?.classList.add('line-through', 'text-gray-400');
                    rowEl?.classList.add('bg-emerald-50/20');
                } else {
                    titleEl?.classList.remove('line-through', 'text-gray-400');
                    rowEl?.classList.remove('bg-emerald-50/20');
                }

                const totalEstEl = document.getElementById('card_total_estimated');
                const totalCompEl = document.getElementById('card_total_completed');
                const totalRemEl = document.getElementById('card_total_remaining');
                const progTextEl = document.getElementById('card_progress_text');
                const progBarEl = document.getElementById('card_progress_bar');

                const footerGrandEl = document.getElementById('footer_grand_total');
                const footerInfoEl = document.getElementById('footer_summary_info');
                const footerBadge = document.getElementById('footer_progress_badge');

                if (totalEstEl) totalEstEl.textContent = formatRupiah(data.total_estimated);
                if (totalCompEl) totalCompEl.textContent = formatRupiah(data.total_completed);
                if (totalRemEl) totalRemEl.textContent = formatRupiah(data.total_remaining);
                if (progTextEl) progTextEl.textContent = data.progress_percentage + '%';
                if (progBarEl) progBarEl.style.width = data.progress_percentage + '%';
                if (footerGrandEl) footerGrandEl.textContent = formatRupiah(data.total_estimated);
                if (footerInfoEl) {
                    footerInfoEl.textContent = `✅ Terbeli: ${formatRupiah(data.total_completed)} | ⏳ Sisa: ${formatRupiah(data.total_remaining)}`;
                }
                if (footerBadge) {
                    footerBadge.textContent = `${data.progress_percentage}% Terpenuhi`;
                }

            } catch (err) {
                console.error(err);
                checkboxEl.checked = !checkboxEl.checked;
                alert('Terjadi kesalahan saat memperbarui status item.');
            }
        }

        // -------------------------------------------------------------------------
        // 6. SEARCH & EXPAND/COLLAPSE CABANG
        // -------------------------------------------------------------------------
        function filterItemsTable() {
            const query = document.getElementById('itemSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#itemsTable tbody tr.item-row');

            rows.forEach(row => {
                const titleText = row.querySelector('.item-title')?.textContent.toLowerCase() || '';
                if (titleText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function toggleBranchRow(itemId) {
            const chevron = document.getElementById(`chevron-${itemId}`);
            const childRows = document.querySelectorAll(`[data-parent-id="${itemId}"]`);

            const isCurrentlyExpanded = !chevron.classList.contains('rotate-180');

            if (isCurrentlyExpanded) {
                chevron.classList.add('rotate-180');
                hideChildrenRecursive(itemId);
            } else {
                chevron.classList.remove('rotate-180');
                showChildrenRecursive(itemId);
            }
        }

        function hideChildrenRecursive(parentId) {
            const children = document.querySelectorAll(`[data-parent-id="${parentId}"]`);
            children.forEach(child => {
                child.style.display = 'none';
                const childId = child.getAttribute('data-id');
                hideChildrenRecursive(childId);
            });
        }

        function showChildrenRecursive(parentId) {
            const children = document.querySelectorAll(`[data-parent-id="${parentId}"]`);
            children.forEach(child => {
                child.style.display = '';
                const childId = child.getAttribute('data-id');
                const childChevron = document.getElementById(`chevron-${childId}`);
                if (!childChevron || !childChevron.classList.contains('rotate-180')) {
                    showChildrenRecursive(childId);
                }
            });
        }

        function toggleAllBranches(expand) {
            const rows = document.querySelectorAll('#itemsTable tbody tr.item-row');
            rows.forEach(row => {
                if (expand) {
                    row.style.display = '';
                } else {
                    const depth = parseInt(row.getAttribute('data-depth') || '0');
                    if (depth > 0) {
                        row.style.display = 'none';
                    }
                }
            });

            const chevrons = document.querySelectorAll('[id^="chevron-"]');
            chevrons.forEach(chevron => {
                if (expand) {
                    chevron.classList.remove('rotate-180');
                } else {
                    chevron.classList.add('rotate-180');
                }
            });
        }

        // -------------------------------------------------------------------------
        // 7. KOLABORASI, SHARE CODE & ONLINE PRESENCE (HEARTBEAT)
        // -------------------------------------------------------------------------
        function openCollaborationModal() {
            document.getElementById('collaborationModal').classList.remove('hidden');
        }

        function closeCollaborationModal() {
            document.getElementById('collaborationModal').classList.add('hidden');
        }

        function copyShareCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                const btnText = document.getElementById('btnCopyCodeText');
                if (btnText) {
                    const original = btnText.textContent;
                    btnText.textContent = 'Tersalin! ✅';
                    setTimeout(() => { btnText.textContent = original; }, 2000);
                }
            }).catch(() => {
                alert('Kode rencana: ' + code);
            });
        }

        function copyShareLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById('btnCopyLink');
                if (btn) {
                    const original = btn.innerHTML;
                    btn.innerHTML = '✅ Link Undangan Tersalin!';
                    setTimeout(() => { btn.innerHTML = original; }, 2000);
                }
            }).catch(() => {
                alert('Link undangan: ' + url);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }

        // Heartbeat interval polling untuk status user online realtime
        function runPresenceHeartbeat() {
            fetch("{{ route('future-planner.heartbeat', $futurePlan) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.active_users) {
                    renderActiveUsers(data.active_users);
                }
            })
            .catch(err => {
                console.debug('Heartbeat ping:', err);
            });
        }

        function renderActiveUsers(users) {
            const container = document.getElementById('activeUsersList');
            if (!container || !users) return;

            if (users.length === 0) {
                container.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-2xs" title="Hanya Anda yang saat ini membuka rencana ini">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                `;
                return;
            }

            container.innerHTML = users.map(u => `
                <div class="relative group user-avatar-badge" data-user-id="${u.id}">
                    <div class="w-8 h-8 rounded-full ${u.color || 'bg-indigo-600'} text-white font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-2xs transition transform hover:scale-110 cursor-pointer select-none">
                        ${escapeHtml(u.initial)}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                    <div class="absolute right-0 top-full mt-2 hidden group-hover:flex flex-col items-center z-50 pointer-events-none">
                        <div class="bg-gray-900 text-white text-[11px] rounded-lg py-1 px-2.5 whitespace-nowrap shadow-xl">
                            <p class="font-semibold">${escapeHtml(u.name)} ${u.is_current ? '(Anda)' : ''}</p>
                            <p class="text-[10px] text-emerald-300 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                                ${escapeHtml(u.role_label || 'Anggota')} • Sedang aktif
                            </p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Jalankan heartbeat berkala setiap 20 detik
        setInterval(runPresenceHeartbeat, 20000);

        // -------------------------------------------------------------------------
        // 8. EKSPOR & IMPOR DATA SPREADSHEET
        // -------------------------------------------------------------------------
        function toggleExportMenu(e) {
            e.stopPropagation();
            const menu = document.getElementById('exportMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('exportDropdownContainer');
            const menu = document.getElementById('exportMenu');
            if (dropdown && menu && !dropdown.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        function updateSelectedFileName(input) {
            const file = input.files && input.files[0];
            const badge = document.getElementById('fileSelectedBadge');
            const nameText = document.getElementById('selectedFileNameText');
            const sizeText = document.getElementById('selectedFileSizeText');
            const prompt = document.getElementById('fileUploadPrompt');

            if (file) {
                if (nameText) nameText.textContent = file.name;
                if (sizeText) {
                    const sizeInKB = (file.size / 1024).toFixed(1);
                    sizeText.textContent = `(${sizeInKB} KB)`;
                }
                if (badge) badge.classList.remove('hidden');
                if (prompt) prompt.textContent = 'Berkas terpilih: ' + file.name;
            } else {
                if (badge) badge.classList.add('hidden');
                if (prompt) prompt.textContent = 'Klik untuk memilih berkas atau seret ke sini';
            }
        }

        // Drag and drop event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('importFileInput');

            if (dropZone && fileInput) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.add('border-indigo-500', 'bg-indigo-50/60');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/60');
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    if (dt && dt.files && dt.files.length) {
                        fileInput.files = dt.files;
                        updateSelectedFileName(fileInput);
                    }
                }, false);
            }
        });

        function handleImportSubmit(e) {
            const btn = document.getElementById('btnSubmitImport');
            const btnText = document.getElementById('btnSubmitImportText');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                if (btnText) {
                    btnText.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sedang Mengimpor Data...
                    `;
                }
            }
        }

        // -------------------------------------------------------------------------
        // 9. GANTT CHART ENGINE & LINIMASA
        // -------------------------------------------------------------------------
        function switchViewMode(mode) {
            currentViewMode = mode;
            localStorage.setItem('futurePlanner_viewMode_' + PLAN_ID, mode);

            const tableContainer = document.getElementById('tableViewContainer');
            const ganttContainer = document.getElementById('ganttViewContainer');
            const tabTable = document.getElementById('tabBtnTable');
            const tabGantt = document.getElementById('tabBtnGantt');

            if (mode === 'gantt') {
                if (tableContainer) tableContainer.classList.add('hidden');
                if (ganttContainer) ganttContainer.classList.remove('hidden');

                if (tabGantt) {
                    tabGantt.className = "inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-indigo-600 text-indigo-700 bg-indigo-50/50 rounded-t-xl";
                }
                if (tabTable) {
                    tabTable.className = "inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300";
                }

                renderGanttChart();
            } else {
                if (tableContainer) tableContainer.classList.remove('hidden');
                if (ganttContainer) ganttContainer.classList.add('hidden');

                if (tabTable) {
                    tabTable.className = "inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-indigo-600 text-indigo-700 bg-indigo-50/50 rounded-t-xl";
                }
                if (tabGantt) {
                    tabGantt.className = "inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 transition -mb-px border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300";
                }
            }
        }

        function setGanttScale(scale) {
            currentGanttScale = scale;
            ['btnScaleDay', 'btnScaleWeek', 'btnScaleMonth'].forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.className = "px-3 py-1 rounded-lg transition text-gray-600 hover:text-gray-900";
                }
            });

            const activeBtn = document.getElementById(scale === 'day' ? 'btnScaleDay' : (scale === 'week' ? 'btnScaleWeek' : 'btnScaleMonth'));
            if (activeBtn) {
                activeBtn.className = "px-3 py-1 rounded-lg transition bg-white text-indigo-700 shadow-2xs font-bold";
            }

            renderGanttChart();
        }

        function syncGanttScroll(pane) {
            const headerScroll = document.getElementById('ganttHeaderScroll');
            if (headerScroll) {
                headerScroll.scrollLeft = pane.scrollLeft;
            }
        }

        function scrollToToday() {
            const marker = document.getElementById('ganttTodayMarker');
            const rightPane = document.getElementById('ganttRightPane');
            if (marker && rightPane) {
                const markerLeft = parseInt(marker.style.left, 10) || 0;
                rightPane.scrollTo({
                    left: Math.max(0, markerLeft - 220),
                    behavior: 'smooth'
                });
            }
        }

        // -------------------------------------------------------------------------
        // MODAL ATUR TANGGAL ITEM
        // -------------------------------------------------------------------------
        function openDateEditModal(itemId) {
            const item = GANTT_ITEMS.find(i => i.id == itemId);
            if (!item) return;

            document.getElementById('dateModalItemId').value = item.id;
            document.getElementById('dateModalItemTitle').textContent = item.title;
            document.getElementById('dateModalStartDate').value = item.start_date || '';
            document.getElementById('dateModalEndDate').value = item.end_date || '';

            updateDateModalDurationPreview();
            document.getElementById('dateEditModal').classList.remove('hidden');
        }

        function closeDateEditModal() {
            document.getElementById('dateEditModal').classList.add('hidden');
        }

        function updateDateModalDurationPreview() {
            const sVal = document.getElementById('dateModalStartDate').value;
            const eVal = document.getElementById('dateModalEndDate').value;
            const textEl = document.getElementById('dateModalDurationText');

            if (!sVal && !eVal) {
                textEl.textContent = 'Belum diatur';
                textEl.className = 'font-bold text-gray-400';
                return;
            }

            if (sVal && eVal) {
                const s = new Date(sVal + 'T00:00:00');
                const e = new Date(eVal + 'T00:00:00');
                if (e < s) {
                    textEl.textContent = 'Tanggal selesai harus >= tanggal mulai';
                    textEl.className = 'font-bold text-rose-600';
                    return;
                }
                const diffDays = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;
                textEl.textContent = `${diffDays} hari kalender`;
                textEl.className = 'font-bold text-indigo-700';
            } else {
                textEl.textContent = '1 hari (hanya satu tanggal)';
                textEl.className = 'font-bold text-amber-600';
            }
        }

        function applyDatePreset(preset) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayStr = `${yyyy}-${mm}-${dd}`;

            let sDate = todayStr;
            let eDate = todayStr;

            if (preset === '1week') {
                const end = new Date(today);
                end.setDate(end.getDate() + 6);
                eDate = end.toISOString().slice(0, 10);
            } else if (preset === '2weeks') {
                const end = new Date(today);
                end.setDate(end.getDate() + 13);
                eDate = end.toISOString().slice(0, 10);
            } else if (preset === '1month') {
                const end = new Date(today);
                end.setDate(end.getDate() + 29);
                eDate = end.toISOString().slice(0, 10);
            }

            document.getElementById('dateModalStartDate').value = sDate;
            document.getElementById('dateModalEndDate').value = eDate;
            updateDateModalDurationPreview();
        }

        async function clearItemDates() {
            const itemId = document.getElementById('dateModalItemId').value;
            if (!itemId) return;

            if (!confirm('Hapus jadwal tanggal untuk item ini?')) return;

            try {
                const res = await fetch(`/future-planner/${PLAN_ID}/items/${itemId}/dates`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: null,
                        end_date: null
                    })
                });

                if (!res.ok) throw new Error('Gagal menghapus jadwal item.');

                const data = await res.json();
                updateLocalItemDates(itemId, null, null, data);
                closeDateEditModal();
            } catch (err) {
                alert(err.message || 'Terjadi kesalahan.');
            }
        }

        async function handleDateFormSubmit(e) {
            e.preventDefault();
            const itemId = document.getElementById('dateModalItemId').value;
            const startDate = document.getElementById('dateModalStartDate').value || null;
            const endDate = document.getElementById('dateModalEndDate').value || null;
            const btn = document.getElementById('btnSaveDateModal');

            if (startDate && endDate && endDate < startDate) {
                alert('Tanggal selesai harus sama atau setelah tanggal mulai.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            try {
                const res = await fetch(`/future-planner/${PLAN_ID}/items/${itemId}/dates`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal menyimpan jadwal item.');
                }

                const data = await res.json();
                updateLocalItemDates(itemId, startDate, endDate, data);
                closeDateEditModal();
            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat menyimpan jadwal.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Simpan Jadwal';
            }
        }

        function updateLocalItemDates(itemId, sDate, eDate, serverData) {
            const target = GANTT_ITEMS.find(i => i.id == itemId);
            if (target) {
                target.start_date = sDate;
                target.end_date = eDate;
                target.effective_start_date = serverData.effective_start_date || sDate;
                target.effective_end_date = serverData.effective_end_date || eDate;
                target.duration_days = serverData.duration_days || (sDate && eDate ? 1 : null);
                target.timeline_status = serverData.timeline_status || 'upcoming';
            }

            // Update badge di tabel juga jika ada
            const tableBadge = document.getElementById(`date-badge-item-${itemId}`);
            if (tableBadge) {
                if (sDate || eDate) {
                    tableBadge.innerHTML = `
                        <button type="button"
                                onclick="openDateEditModal(${itemId})"
                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 hover:opacity-80 transition"
                                title="Ubah Jadwal Linimasa">
                            <span>📅</span>
                            <span>${formatDateMini(sDate)} - ${formatDateMini(eDate)}</span>
                        </button>
                    `;
                } else {
                    tableBadge.innerHTML = `
                        <button type="button"
                                onclick="openDateEditModal(${itemId})"
                                class="text-[10px] text-gray-400 hover:text-indigo-600 transition inline-flex items-center gap-0.5"
                                title="Tambahkan tanggal mulai & selesai untuk Gantt Chart">
                            <span>📅 + Jadwal</span>
                        </button>
                    `;
                }
            }

            renderGanttChart();
        }

        // -------------------------------------------------------------------------
        // GANTT CHART RENDERING ENGINE
        // -------------------------------------------------------------------------
        function renderGanttChart() {
            const headerContainer = document.getElementById('ganttTimelineHeader');
            const leftPane = document.getElementById('ganttLeftPane');
            const gridBg = document.getElementById('ganttGridBackground');
            const barsContainer = document.getElementById('ganttBarsContainer');
            const canvas = document.getElementById('ganttGridCanvas');
            const todayMarker = document.getElementById('ganttTodayMarker');

            if (!headerContainer || !leftPane || !barsContainer) return;

            const todayStr = '{{ now()->format("Y-m-d") }}';
            const todayObj = new Date(todayStr + 'T00:00:00');

            // 1. Tentukan batas tanggal linimasa
            let minDate = null;
            let maxDate = null;
            let scheduledCount = 0;

            GANTT_ITEMS.forEach(item => {
                const s = item.effective_start_date || item.start_date;
                const e = item.effective_end_date || item.end_date;
                if (s || e) {
                    scheduledCount++;
                    const sObj = s ? new Date(s + 'T00:00:00') : new Date(e + 'T00:00:00');
                    const eObj = e ? new Date(e + 'T00:00:00') : new Date(s + 'T00:00:00');

                    if (!minDate || sObj < minDate) minDate = new Date(sObj);
                    if (!maxDate || eObj > maxDate) maxDate = new Date(eObj);
                }
            });

            // Fallback jika belum ada yang diatur tanggalnya
            if (!minDate) {
                minDate = new Date(todayObj);
                minDate.setDate(minDate.getDate() - 3);
            } else {
                minDate.setDate(minDate.getDate() - 3);
            }

            if (!maxDate) {
                maxDate = new Date(todayObj);
                maxDate.setDate(maxDate.getDate() + 25);
            } else {
                maxDate.setDate(maxDate.getDate() + 6);
            }

            // Pastikan Hari Ini tercakup di linimasa
            if (todayObj < minDate) {
                minDate = new Date(todayObj);
                minDate.setDate(minDate.getDate() - 3);
            }
            if (todayObj > maxDate) {
                maxDate = new Date(todayObj);
                maxDate.setDate(maxDate.getDate() + 6);
            }

            // Update badge info rentang
            const dateRangeSummary = document.getElementById('ganttDateRangeSummary');
            const scheduledSummary = document.getElementById('ganttScheduledCountSummary');
            if (dateRangeSummary) {
                const totalSpanDays = Math.round((maxDate - minDate) / (1000 * 60 * 60 * 24)) + 1;
                dateRangeSummary.textContent = `${formatDateShort(minDate)} s/d ${formatDateShort(maxDate)} (${totalSpanDays} hari cakupan linimasa)`;
            }
            if (scheduledSummary) {
                scheduledSummary.textContent = `${scheduledCount} dari ${GANTT_ITEMS.length} item berjadwal`;
            }

            // 2. Tentukan lebar kolom dan langkah waktu
            let colWidth = 36;
            if (currentGanttScale === 'week') colWidth = 70;
            if (currentGanttScale === 'month') colWidth = 110;

            const msPerDay = 1000 * 60 * 60 * 24;
            const totalDays = Math.max(10, Math.round((maxDate - minDate) / msPerDay) + 1);

            // 3. Bangun Header Linimasa
            let headerHtml = '';
            let gridBgHtml = '';
            let totalCanvasWidth = 0;

            if (currentGanttScale === 'day') {
                totalCanvasWidth = totalDays * colWidth;

                // Hitung pengelompokan Bulan
                const monthsGroup = [];
                let curMonth = -1;
                let curMonthYear = -1;
                let curMonthDays = 0;

                const dayColsHtml = [];

                for (let d = 0; d < totalDays; d++) {
                    const cDate = new Date(minDate);
                    cDate.setDate(minDate.getDate() + d);
                    const m = cDate.getMonth();
                    const y = cDate.getFullYear();
                    const dayNum = cDate.getDate();
                    const dayOfWeek = cDate.getDay(); // 0 = Sun, 6 = Sat
                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const isToday = (cDate.getTime() === todayObj.getTime());

                    if (m !== curMonth || y !== curMonthYear) {
                        if (curMonth !== -1) {
                            monthsGroup.push({
                                label: formatMonthYear(new Date(curMonthYear, curMonth, 1)),
                                days: curMonthDays,
                                width: curMonthDays * colWidth
                            });
                        }
                        curMonth = m;
                        curMonthYear = y;
                        curMonthDays = 1;
                    } else {
                        curMonthDays++;
                    }

                    const dayNames = ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'];
                    dayColsHtml.push(`
                        <div class="shrink-0 flex flex-col items-center justify-center border-r border-gray-100 ${isWeekend ? 'bg-gray-100/60 text-gray-400' : 'text-gray-600'} ${isToday ? 'bg-rose-50 text-rose-700 font-bold' : ''}" style="width: ${colWidth}px; height: 38px;">
                            <span class="text-[9px] uppercase leading-none">${dayNames[dayOfWeek]}</span>
                            <span class="text-[11px] font-bold leading-tight ${isToday ? 'text-rose-600' : ''}">${dayNum}</span>
                        </div>
                    `);

                    gridBgHtml += `
                        <div class="absolute top-0 bottom-0 border-r border-gray-100/70 ${isWeekend ? 'bg-gray-50/40' : ''}" style="left: ${d * colWidth}px; width: ${colWidth}px;"></div>
                    `;
                }

                if (curMonthDays > 0) {
                    monthsGroup.push({
                        label: formatMonthYear(new Date(curMonthYear, curMonth, 1)),
                        days: curMonthDays,
                        width: curMonthDays * colWidth
                    });
                }

                const monthsRowHtml = monthsGroup.map(mg => `
                    <div class="shrink-0 px-3 py-1.5 border-r border-gray-200 border-b text-[11px] font-bold text-gray-700 whitespace-nowrap bg-gray-50" style="width: ${mg.width}px;">
                        ${mg.label}
                    </div>
                `).join('');

                headerHtml = `
                    <div style="width: ${totalCanvasWidth}px;">
                        <div class="flex">${monthsRowHtml}</div>
                        <div class="flex">${dayColsHtml.join('')}</div>
                    </div>
                `;

            } else if (currentGanttScale === 'week') {
                const totalWeeks = Math.ceil(totalDays / 7);
                totalCanvasWidth = totalWeeks * colWidth;

                const weekColsHtml = [];
                for (let w = 0; w < totalWeeks; w++) {
                    const wDate = new Date(minDate);
                    wDate.setDate(minDate.getDate() + (w * 7));
                    weekColsHtml.push(`
                        <div class="shrink-0 flex flex-col items-center justify-center border-r border-gray-200 text-gray-600 px-1 py-1" style="width: ${colWidth}px; height: 48px;">
                            <span class="text-[10px] font-bold text-indigo-700">M${w + 1}</span>
                            <span class="text-[10px] text-gray-400 font-mono">${wDate.getDate()} ${getMonthAbbr(wDate.getMonth())}</span>
                        </div>
                    `);
                    gridBgHtml += `
                        <div class="absolute top-0 bottom-0 border-r border-gray-100/80" style="left: ${w * colWidth}px; width: ${colWidth}px;"></div>
                    `;
                }

                headerHtml = `
                    <div class="flex bg-gray-50 border-b border-gray-200" style="width: ${totalCanvasWidth}px;">
                        ${weekColsHtml.join('')}
                    </div>
                `;

            } else {
                // Bulan
                const totalMonths = Math.max(3, Math.ceil(totalDays / 30));
                totalCanvasWidth = totalMonths * colWidth;

                const monthColsHtml = [];
                for (let m = 0; m < totalMonths; m++) {
                    const mDate = new Date(minDate);
                    mDate.setMonth(minDate.getMonth() + m);
                    monthColsHtml.push(`
                        <div class="shrink-0 flex flex-col items-center justify-center border-r border-gray-200 text-gray-700 font-bold text-xs" style="width: ${colWidth}px; height: 48px;">
                            <span>${formatMonthYear(mDate)}</span>
                        </div>
                    `);
                    gridBgHtml += `
                        <div class="absolute top-0 bottom-0 border-r border-gray-100" style="left: ${m * colWidth}px; width: ${colWidth}px;"></div>
                    `;
                }

                headerHtml = `
                    <div class="flex bg-gray-50 border-b border-gray-200" style="width: ${totalCanvasWidth}px;">
                        ${monthColsHtml.join('')}
                    </div>
                `;
            }

            headerContainer.innerHTML = headerHtml;
            canvas.style.width = `${totalCanvasWidth}px`;
            gridBg.innerHTML = gridBgHtml;

            // 4. Position Today Marker
            const todayDiffDays = Math.round((todayObj - minDate) / msPerDay);
            if (todayDiffDays >= 0 && todayDiffDays <= totalDays) {
                let todayPx = 0;
                if (currentGanttScale === 'day') {
                    todayPx = (todayDiffDays * colWidth) + (colWidth / 2);
                } else if (currentGanttScale === 'week') {
                    todayPx = (todayDiffDays / 7) * colWidth;
                } else {
                    todayPx = (todayDiffDays / 30) * colWidth;
                }
                todayMarker.style.left = `${todayPx}px`;
                todayMarker.classList.remove('hidden');
            } else {
                todayMarker.classList.add('hidden');
            }

            // 5. Render Left Pane & Right Timeline Rows
            let leftPaneHtml = '';
            let barsHtml = '';

            GANTT_ITEMS.forEach((item, idx) => {
                const sDate = item.effective_start_date || item.start_date;
                const eDate = item.effective_end_date || item.end_date;
                const depth = item.depth || 0;
                const indent = depth * 18;
                const hasDates = !!(sDate || eDate);

                // Baris Left Pane
                leftPaneHtml += `
                    <div class="flex items-center justify-between px-3 h-12 hover:bg-indigo-50/40 transition gantt-row-left" data-item-id="${item.id}">
                        <div class="flex items-center gap-1.5 min-w-0" style="padding-left: ${indent}px;">
                            <input type="checkbox"
                                   ${item.is_completed ? 'checked' : ''}
                                   onchange="toggleItemStatus(${item.id}, this)"
                                   class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer shrink-0">
                            ${depth > 0 ? '<span class="text-indigo-400 font-mono text-[11px] select-none">└──</span>' : ''}
                            <span class="inline-flex items-center px-1 rounded text-[9px] font-bold ${item.has_children ? 'bg-indigo-100 text-indigo-900' : 'bg-gray-100 text-gray-600'} shrink-0">
                                ${item.has_children ? '📁' : 'L' + (depth + 1)}
                            </span>
                            <span class="text-xs truncate font-medium text-gray-800 ${item.is_completed ? 'line-through text-gray-400' : (item.has_children ? 'font-bold text-indigo-950' : '')}" title="${escapeHtml(item.title)}">
                                ${escapeHtml(item.title)}
                            </span>
                        </div>
                        <div class="shrink-0 flex items-center gap-1">
                            ${hasDates ? `
                                <button type="button"
                                        onclick="openDateEditModal(${item.id})"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold ${getStatusBadgeClass(item.timeline_status)} hover:opacity-80 transition"
                                        title="Ubah Jadwal: ${sDate || '?'} s/d ${eDate || '?'}">
                                    <span>${formatDateMini(sDate)} - ${formatDateMini(eDate)}</span>
                                </button>
                            ` : `
                                <button type="button"
                                        onclick="openDateEditModal(${item.id})"
                                        class="text-[10px] font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 px-1.5 py-0.5 rounded border border-dashed border-amber-300 transition"
                                        title="Tambahkan tanggal mulai & selesai">
                                    + Atur
                                </button>
                            `}
                        </div>
                    </div>
                `;

                // Baris Right Pane (Canvas)
                if (hasDates) {
                    const itemStart = new Date((sDate || eDate) + 'T00:00:00');
                    const itemEnd = new Date((eDate || sDate) + 'T00:00:00');

                    let leftPx = 0;
                    let widthPx = 0;

                    if (currentGanttScale === 'day') {
                        const startDiff = Math.max(0, Math.round((itemStart - minDate) / msPerDay));
                        const endDiff = Math.round((itemEnd - minDate) / msPerDay);
                        const dur = Math.max(1, endDiff - startDiff + 1);
                        leftPx = startDiff * colWidth;
                        widthPx = Math.max(colWidth, dur * colWidth);
                    } else if (currentGanttScale === 'week') {
                        const startDiff = Math.max(0, (itemStart - minDate) / msPerDay);
                        const endDiff = (itemEnd - minDate) / msPerDay;
                        const dur = Math.max(1, endDiff - startDiff + 1);
                        leftPx = (startDiff / 7) * colWidth;
                        widthPx = Math.max(24, (dur / 7) * colWidth);
                    } else {
                        const startDiff = Math.max(0, (itemStart - minDate) / msPerDay);
                        const endDiff = (itemEnd - minDate) / msPerDay;
                        const dur = Math.max(1, endDiff - startDiff + 1);
                        leftPx = (startDiff / 30) * colWidth;
                        widthPx = Math.max(28, (dur / 30) * colWidth);
                    }

                    const barColorClass = getStatusBarGradientClass(item.timeline_status, item.has_children);
                    const durDays = item.duration_days || Math.round((itemEnd - itemStart) / msPerDay) + 1;
                    const tooltip = `${item.title}\nJadwal: ${sDate || '?'} s/d ${eDate || '?'}\nDurasi: ${durDays} hari\nStatus: ${getStatusLabel(item.timeline_status)}`;

                    barsHtml += `
                        <div class="h-12 flex items-center relative hover:bg-indigo-50/30 transition gantt-row-right" data-item-id="${item.id}">
                            <div class="absolute h-7 rounded-lg shadow-2xs flex items-center px-2 text-white text-[11px] font-semibold cursor-pointer transition transform hover:scale-[1.01] hover:shadow-md select-none overflow-hidden ${barColorClass}"
                                 style="left: ${leftPx}px; width: ${widthPx}px;"
                                 onclick="openDateEditModal(${item.id})"
                                 title="${escapeHtml(tooltip)}">
                                ${item.has_children ? `
                                    <div class="absolute inset-0 border-t-2 border-b-2 border-indigo-900/60 pointer-events-none"></div>
                                ` : ''}
                                <span class="truncate z-10">${escapeHtml(item.title)}</span>
                                <span class="ml-1 text-[9px] opacity-80 whitespace-nowrap z-10">(${durDays}h)</span>
                            </div>
                        </div>
                    `;
                } else {
                    barsHtml += `
                        <div class="h-12 flex items-center px-3 relative hover:bg-indigo-50/30 transition gantt-row-right" data-item-id="${item.id}">
                            <button type="button"
                                    onclick="openDateEditModal(${item.id})"
                                    class="text-[11px] font-medium text-gray-400 hover:text-indigo-600 border border-dashed border-gray-300 hover:border-indigo-400 bg-white hover:bg-indigo-50/50 rounded-lg px-2.5 py-1 transition flex items-center gap-1">
                                <span>📅 + Klik untuk atur tanggal linimasa</span>
                            </button>
                        </div>
                    `;
                }
            });

            leftPane.innerHTML = leftPaneHtml;
            barsContainer.innerHTML = barsHtml;

            // Row hover synchronizer
            bindGanttRowHoverEvents();
        }

        function bindGanttRowHoverEvents() {
            const leftRows = document.querySelectorAll('.gantt-row-left');
            const rightRows = document.querySelectorAll('.gantt-row-right');

            leftRows.forEach(lr => {
                lr.addEventListener('mouseenter', () => {
                    const id = lr.dataset.itemId;
                    const rr = document.querySelector(`.gantt-row-right[data-item-id="${id}"]`);
                    if (rr) rr.classList.add('bg-indigo-50/50');
                });
                lr.addEventListener('mouseleave', () => {
                    const id = lr.dataset.itemId;
                    const rr = document.querySelector(`.gantt-row-right[data-item-id="${id}"]`);
                    if (rr) rr.classList.remove('bg-indigo-50/50');
                });
            });

            rightRows.forEach(rr => {
                rr.addEventListener('mouseenter', () => {
                    const id = rr.dataset.itemId;
                    const lr = document.querySelector(`.gantt-row-left[data-item-id="${id}"]`);
                    if (lr) lr.classList.add('bg-indigo-50/50');
                });
                rr.addEventListener('mouseleave', () => {
                    const id = rr.dataset.itemId;
                    const lr = document.querySelector(`.gantt-row-left[data-item-id="${id}"]`);
                    if (lr) lr.classList.remove('bg-indigo-50/50');
                });
            });
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'completed': return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                case 'in_progress': return 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                case 'overdue': return 'bg-rose-50 text-rose-700 border border-rose-200';
                case 'upcoming': return 'bg-slate-100 text-slate-700 border border-slate-200';
                default: return 'bg-amber-50 text-amber-700 border border-dashed border-amber-300';
            }
        }

        function getStatusBarGradientClass(status, isParent) {
            if (isParent) {
                return 'bg-gradient-to-r from-slate-700 via-indigo-900 to-slate-800 text-white border border-slate-900';
            }
            switch (status) {
                case 'completed': return 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white';
                case 'in_progress': return 'bg-gradient-to-r from-indigo-500 via-indigo-600 to-blue-600 text-white';
                case 'overdue': return 'bg-gradient-to-r from-rose-500 to-red-600 text-white';
                case 'upcoming': return 'bg-gradient-to-r from-slate-400 to-gray-500 text-white';
                default: return 'bg-gradient-to-r from-amber-400 to-orange-500 text-white';
            }
        }

        function getStatusLabel(status) {
            switch (status) {
                case 'completed': return 'Selesai';
                case 'in_progress': return 'Sedang Berjalan';
                case 'overdue': return 'Terlambat';
                case 'upcoming': return 'Akan Datang';
                default: return 'Belum Diatur';
            }
        }

        function formatDateShort(date) {
            if (!date) return '';
            const d = new Date(date);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        function formatDateMini(str) {
            if (!str) return '?';
            const d = new Date(str + 'T00:00:00');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]}`;
        }

        function formatMonthYear(d) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return `${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        function getMonthAbbr(m) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return months[m] || '';
        }

        // Restore view mode on initial load
        document.addEventListener('DOMContentLoaded', () => {
            if (currentViewMode === 'gantt') {
                switchViewMode('gantt');
            }
        });
    </script>
</x-app-layout>
