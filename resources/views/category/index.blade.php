<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🏷️ Kategori Transaksi</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Kelola kategori kustom Anda untuk pemasukan & pengeluaran yang lebih rapi</p>
            </div>
            <button type="button" onclick="openCreateCategoryModal()"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Kategori Kustom
            </button>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white shrink-0">✓</div>
                <p class="text-green-800 text-sm font-semibold flex-1">{{ session('success') }}</p>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white shrink-0">!</div>
                <p class="text-red-800 text-sm font-semibold flex-1">{{ session('error') }}</p>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">✕</button>
            </div>
        @endif

        @php
            $colorMap = [
                'amber'   => ['bg' => '#FFFBEB', 'text' => '#B45309', 'border' => '#FDE68A'],
                'pink'    => ['bg' => '#FDF2F8', 'text' => '#BE185D', 'border' => '#FBCFE8'],
                'blue'    => ['bg' => '#EFF6FF', 'text' => '#1D4ED8', 'border' => '#BFDBFE'],
                'yellow'  => ['bg' => '#FEFCE8', 'text' => '#A16207', 'border' => '#FEF08A'],
                'purple'  => ['bg' => '#FAF5FF', 'text' => '#6D28D9', 'border' => '#E9D5FF'],
                'red'     => ['bg' => '#FEF2F2', 'text' => '#B91C1C', 'border' => '#FEE2E2'],
                'gray'    => ['bg' => '#F9FAFB', 'text' => '#374151', 'border' => '#E5E7EB'],
                'emerald' => ['bg' => '#ECFDF5', 'text' => '#047857', 'border' => '#A7F3D0'],
                'indigo'  => ['bg' => '#EEF2FF', 'text' => '#4338CA', 'border' => '#C7D2FE'],
                'teal'    => ['bg' => '#F0FDFA', 'text' => '#0F766E', 'border' => '#99F6E4'],
                'orange'  => ['bg' => '#FFF7ED', 'text' => '#C2410C', 'border' => '#FFEDD5'],
            ];
        @endphp

        <!-- Kategori Kustom Pengguna -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Kategori Kustom Saya ({{ $customCategories->count() }})</h2>
                    <p class="text-xs text-gray-500">Kategori yang Anda buat sendiri—dapat diubah dan dihapus kapan saja</p>
                </div>
            </div>

            @if($customCategories->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($customCategories as $cat)
                        @php $c = $colorMap[$cat->color ?? 'gray'] ?? $colorMap['gray']; @endphp
                        <div class="flex items-center justify-between gap-3 p-4 rounded-2xl border border-gray-100 hover:border-blue-200 transition bg-white shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border"
                                     style="background-color: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }};">
                                    <i class="fas {{ $cat->icon ?? 'fa-tag' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $cat->name }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->type === 'Cash In' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $cat->type === 'Cash In' ? 'Pemasukan (Cash In)' : 'Pengeluaran (Cash Out)' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                @php
                                    $catData = [
                                        'id'    => $cat->id,
                                        'name'  => $cat->name,
                                        'type'  => $cat->type,
                                        'color' => $cat->color ?? 'blue',
                                        'icon'  => $cat->icon ?? 'fa-tag',
                                    ];
                                @endphp
                                <button type="button" onclick='openEditCategoryModal(@json($catData))'
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition" title="Edit Kategori">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button type="button" onclick="openDeleteCategoryModal({{ $cat->id }}, {{ json_encode($cat->name) }})"
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Kategori">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <p class="text-sm font-semibold text-gray-600">Anda belum membuat kategori kustom.</p>
                    <p class="text-xs text-gray-400 mt-1">Klik tombol "Tambah Kategori Kustom" di kanan atas untuk membuat kategori baru.</p>
                </div>
            @endif
        </div>

        <!-- Kategori Bawaan Sistem -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-lg font-bold text-gray-800">Kategori Bawaan Sistem ({{ $systemCategories->count() }})</h2>
                <p class="text-xs text-gray-500">Kategori standar yang tersedia otomatis untuk seluruh pencatatan transaksi & anggaran</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($systemCategories as $sysCat)
                    @php $c = $colorMap[$sysCat->color ?? 'gray'] ?? $colorMap['gray']; @endphp
                    <div class="flex items-center gap-3 p-3 rounded-2xl border border-gray-100 bg-gray-50/40">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
                             style="background-color: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }};">
                            <i class="fas {{ $sysCat->icon ?? 'fa-tag' }} text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ $sysCat->name }}</p>
                            <span class="text-[10px] font-semibold {{ $sysCat->type === 'Cash In' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $sysCat->type }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Smart Rules (Auto-Kategorisasi) -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">⚡ Smart Rules (Auto-Kategorisasi) ({{ ($rules ?? collect())->count() }})</h2>
                    <p class="text-xs text-gray-500">Aturan kata kunci otomatis untuk input manual, Import Excel, dan Import PDF e-statement</p>
                </div>
            </div>

            <!-- Form Tambah Rule -->
            <form method="POST" action="{{ route('category.rules.store') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 bg-gray-50/70 p-4 rounded-2xl border border-gray-100">
                @csrf
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Kata Kunci (pisahkan dengan koma)</label>
                    <input type="text" name="keyword" required placeholder="Contoh: gojek, grab, maxim"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Kategori Target</label>
                    <select name="category_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach(($allCategories ?? collect()) as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Pos Anggaran (Opsional)</label>
                    <select name="budget_item_id" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Otomatis / Tidak ada --</option>
                        @foreach(($activeBudgetItems ?? collect()) as $bItem)
                            <option value="{{ $bItem->id }}">{{ $bItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                        + Tambah Rule
                    </button>
                </div>
            </form>

            <!-- Daftar Smart Rules -->
            @if(($rules ?? collect())->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach($rules as $rule)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl border {{ $rule->is_active ? 'border-indigo-100 bg-indigo-50/20' : 'border-gray-100 bg-gray-50 opacity-60' }}">
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-white border border-gray-200 text-xs font-mono font-bold text-gray-800">
                                        "{{ $rule->keyword }}"
                                    </span>
                                    <span class="text-xs text-gray-400">→</span>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-800 text-xs font-bold">
                                        {{ $rule->category?->name ?? 'Kategori Dihapus' }}
                                    </span>
                                    @if($rule->budgetItem)
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 text-[11px] font-semibold">
                                            Pos: {{ $rule->budgetItem->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('category.rules.toggle', $rule->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl text-[11px] font-bold border transition {{ $rule->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' }}">
                                        {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('category.rules.destroy', $rule->id) }}" onsubmit="return confirm('Hapus Smart Rule ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-center text-gray-400 py-4">Belum ada Smart Rule. Tambahkan kata kunci seperti <code>gojek, grab</code> atau <code>indomaret, alfamart</code> di atas.</p>
            @endif
        </div>
    </main>

    <!-- Modal Create / Edit Kategori -->
    <div id="modal-category" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeCategoryModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="cat-modal-title" class="text-xl font-bold text-gray-800">Tambah Kategori Kustom</h3>
                    <button type="button" onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form id="form-category" action="{{ route('category.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="cat-form-method" value="POST">

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="cat-name" required placeholder="Contoh: Kopi & Nongkrong / Bonus Proyek"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tipe Transaksi <span class="text-red-500">*</span></label>
                        <select name="type" id="cat-type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="Cash Out">Pengeluaran (Cash Out)</option>
                            <option value="Cash In">Pemasukan (Cash In)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Warna Badge</label>
                            <select name="color" id="cat-color" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                @foreach($availableColors as $cKey => $cLabel)
                                    <option value="{{ $cKey }}">{{ $cLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Ikon</label>
                            <select name="icon" id="cat-icon" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                @foreach($availableIcons as $iKey => $iLabel)
                                    <option value="{{ $iKey }}">{{ $iLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeCategoryModal()" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200">
                            Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Kategori -->
    <div id="modal-delete-category" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeDeleteCategoryModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-center align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Hapus Kategori?</h3>
                <p class="text-sm text-gray-500 mb-5">Kategori <strong id="del-cat-name" class="text-gray-800"></strong> akan dinonaktifkan dari daftar pilihan baru (transaksi lama tetap aman).</p>

                <form id="form-delete-category" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteCategoryModal()" class="flex-1 py-2.5 px-4 bg-gray-100 rounded-xl text-sm font-semibold text-gray-700">
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

<script>
    function openCreateCategoryModal() {
        document.getElementById('cat-modal-title').textContent = 'Tambah Kategori Kustom';
        document.getElementById('form-category').action = "{{ route('category.store') }}";
        document.getElementById('cat-form-method').value = 'POST';
        document.getElementById('cat-name').value = '';
        document.getElementById('cat-type').value = 'Cash Out';
        document.getElementById('cat-color').value = 'blue';
        document.getElementById('cat-icon').value = 'fa-tag';
        document.getElementById('modal-category').classList.remove('hidden');
    }

    function openEditCategoryModal(cat) {
        document.getElementById('cat-modal-title').textContent = 'Edit Kategori Kustom';
        document.getElementById('form-category').action = '/kategori/' + cat.id;
        document.getElementById('cat-form-method').value = 'PUT';
        document.getElementById('cat-name').value = cat.name || '';
        document.getElementById('cat-type').value = cat.type || 'Cash Out';
        document.getElementById('cat-color').value = cat.color || 'blue';
        document.getElementById('cat-icon').value = cat.icon || 'fa-tag';
        document.getElementById('modal-category').classList.remove('hidden');
    }

    function closeCategoryModal() {
        document.getElementById('modal-category').classList.add('hidden');
    }

    function openDeleteCategoryModal(id, name) {
        document.getElementById('form-delete-category').action = '/kategori/' + id;
        document.getElementById('del-cat-name').textContent = name;
        document.getElementById('modal-delete-category').classList.remove('hidden');
    }

    function closeDeleteCategoryModal() {
        document.getElementById('modal-delete-category').classList.add('hidden');
    }
</script>
