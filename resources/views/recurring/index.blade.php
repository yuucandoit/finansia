<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🔁 Transaksi Berulang (Rutin)</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Otomatiskan pencatatan gaji, listrik, internet, cicilan, dan langganan berkala</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-recurring').classList.remove('hidden')"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 transition-all">
                + Tambah Jadwal Rutin
            </button>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">✓</span>
                <p class="text-emerald-800 text-sm font-semibold flex-1">{{ session('success') }}</p>
                <button onclick="this.parentElement.remove()" class="text-emerald-500">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0">!</span>
                <p class="text-rose-800 text-sm font-semibold flex-1">{{ session('error') }}</p>
                <button onclick="this.parentElement.remove()" class="text-rose-500">✕</button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-gray-500 uppercase">Total Jadwal Aktif</p>
                <p class="text-2xl font-extrabold text-indigo-700 mt-1">{{ $recurrings->where('is_active', true)->count() }} Jadwal</p>
            </div>
            <div class="bg-emerald-50/60 rounded-2xl border border-emerald-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-emerald-700 uppercase">Pemasukan Rutin Aktif</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">+ Rp {{ number_format($activeMonthlyIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-rose-50/60 rounded-2xl border border-rose-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-rose-700 uppercase">Tagihan / Pengeluaran Rutin Aktif</p>
                <p class="text-2xl font-extrabold text-rose-700 mt-1">- Rp {{ number_format($activeMonthlyOut, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Recurring List -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-3">
            @forelse($recurrings as $rec)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl border {{ $rec->is_active ? 'border-gray-100 bg-white' : 'border-gray-100 bg-gray-50 opacity-75' }} hover:border-indigo-200 transition">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 font-bold {{ $rec->type === 'Cash In' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            {{ $rec->type === 'Cash In' ? '↓' : '↑' }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-gray-900">{{ $rec->description }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $rec->frequency_label }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rec->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $rec->is_active ? 'Aktif' : 'Dijeda' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Rekening: <strong>{{ $rec->savingAccount->name ?? '-' }}</strong>
                                @if($rec->category)
                                    • Kategori: <strong>{{ $rec->category->name }}</strong>
                                @endif
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                Jadwal Berikutnya: <strong class="text-indigo-600">{{ $rec->next_run_date->format('d M Y') }}</strong>
                                @if($rec->last_run_date)
                                    • Terakhir dijalankan: {{ $rec->last_run_date->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
                        <span class="text-base font-extrabold {{ $rec->type === 'Cash In' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $rec->type === 'Cash In' ? '+' : '-' }} Rp {{ number_format($rec->amount, 0, ',', '.') }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('recurring.run', $rec) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition" title="Eksekusi sekarang & buat transaksi">
                                    ▶ Jalankan
                                </button>
                            </form>
                            <form action="{{ route('recurring.toggle', $rec) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition">
                                    {{ $rec->is_active ? 'Jeda' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('recurring.destroy', $rec) }}" method="POST" onsubmit="return confirm('Hapus jadwal rutin ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus">
                                    ✕
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 text-sm">
                    Belum ada jadwal transaksi rutin. Klik "+ Tambah Jadwal Rutin" untuk membuat jadwal otomatis.
                </div>
            @endforelse
        </div>
    </main>

    <!-- Modal Tambah Jadwal Rutin -->
    <div id="modal-recurring" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="document.getElementById('modal-recurring').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Tambah Jadwal Transaksi Berulang</h3>
                    <button type="button" onclick="document.getElementById('modal-recurring').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form action="{{ route('recurring.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Deskripsi <span class="text-red-500">*</span></label>
                        <input type="text" name="description" required placeholder="Contoh: Tagihan Internet WiFi / Gaji Bulanan"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tipe</label>
                            <select name="type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                <option value="Cash Out">Pengeluaran (Cash Out)</option>
                                <option value="Cash In">Pemasukan (Cash In)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" min="1000" step="1000" required placeholder="350000"
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Rekening <span class="text-red-500">*</span></label>
                            <select name="savings_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                @foreach($savings as $sav)
                                    <option value="{{ $sav->id }}">{{ $sav->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Kategori</label>
                            <select name="category_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                <option value="">-- Opsional --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Frekuensi</label>
                            <select name="frequency" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                <option value="monthly">Bulanan</option>
                                <option value="weekly">Mingguan</option>
                                <option value="daily">Harian</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Mulai Tanggal</label>
                            <input type="date" name="next_run_date" value="{{ now()->toDateString() }}" required
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('modal-recurring').classList.add('hidden')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
