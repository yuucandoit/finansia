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
            <div class="flex items-center gap-2">
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

                                            <span class="font-medium text-gray-900 item-title {{ $isCompleted ? 'line-through text-gray-400' : ($hasChildren ? 'font-bold text-indigo-950' : '') }}" id="title-item-{{ $item->id }}">
                                                {{ $item->title }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Qty -->
                                    <td class="py-3 px-3 text-center font-semibold text-gray-700 align-middle" id="qty-item-{{ $item->id }}">
                                        {{ $item->quantity }}
                                    </td>

                                    <!-- Harga Satuan -->
                                    <td class="py-3 px-4 text-right text-gray-600 align-middle" id="price-item-{{ $item->id }}">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>

                                    <!-- Total Subtotal (Rollup jika Parent) -->
                                    <td class="py-3 px-4 text-right align-middle" id="subtotal-item-{{ $item->id }}">
                                        <div class="font-bold {{ $hasChildren ? 'text-indigo-700 text-sm' : 'text-gray-900' }}">
                                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                                        </div>
                                        @if($hasChildren)
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

                                            <!-- 4. Tombol Edit Baris Inline -->
                                            <button type="button"
                                                    onclick="enableInlineEditRow({{ $item->id }}, '{{ addslashes($item->title) }}', {{ $item->quantity }}, {{ (float)$item->unit_price }}, '{{ addslashes($item->notes ?? '') }}', {{ $depth }})"
                                                    class="p-1 text-gray-400 hover:text-indigo-600 rounded hover:bg-gray-100 transition"
                                                    title="Edit Baris Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>

                                            <!-- 5. Tombol Hapus Baris -->
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

        </div>
    </div>

    <!-- Script JavaScript untuk Interaktivitas Spreadsheet & Parent-Child Navigation -->
    <script>
        const PLAN_ID = {{ $futurePlan->id }};
        const CSRF_TOKEN = '{{ csrf_token() }}';

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
                        parent_id: parentId
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

        function enableInlineEditRow(itemId, currentTitle, currentQty, currentPrice, currentNotes, depth) {
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
                        notes: notes
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
    </script>
</x-app-layout>
