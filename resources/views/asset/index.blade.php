<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">📈 Valuasi Aset & Investasi</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Lacak portofolio emas, reksadana, saham, valas, dan hitung total kekayaan bersih (Net Worth) Anda</p>
            </div>
            <button type="button" onclick="openCreateAssetModal()"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm transition-all">
                + Tambah Aset Baru
            </button>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">✓</span>
                <p class="text-emerald-800 text-sm font-semibold flex-1">{{ session('success') }}</p>
                <button onclick="this.parentElement.remove()" class="text-emerald-500">✕</button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-xs font-bold text-blue-100 uppercase tracking-wider">Estimasi Net Worth</p>
                <p class="text-2xl font-extrabold mt-1">Rp {{ number_format($netWorth, 0, ',', '.') }}</p>
                <p class="text-[11px] text-blue-100 mt-1">
                    Tabungan Rp {{ number_format($totalSavingsBalance, 0, ',', '.') }} + Aset - Hutang
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-gray-500 uppercase">Total Nilai Pasar Aset</p>
                <p class="text-2xl font-extrabold text-indigo-700 mt-1">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $assets->count() }} instrumen tercatat</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-gray-500 uppercase">Total Modal Beli</p>
                <p class="text-2xl font-extrabold text-gray-800 mt-1">Rp {{ number_format($totalAssetCost, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">Akumulasi nilai perolehan</p>
            </div>

            <div class="rounded-2xl border p-5 shadow-2xs {{ $totalUnrealizedGain >= 0 ? 'bg-emerald-50/70 border-emerald-100' : 'bg-rose-50/70 border-rose-100' }}">
                <p class="text-xs font-bold uppercase {{ $totalUnrealizedGain >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Potensi Untung / Rugi</p>
                <p class="text-2xl font-extrabold mt-1 {{ $totalUnrealizedGain >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $totalUnrealizedGain >= 0 ? '+' : '-' }} Rp {{ number_format(abs($totalUnrealizedGain), 0, ',', '.') }}
                </p>
                <p class="text-xs font-bold mt-1 {{ $totalUnrealizedGain >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $totalUnrealizedGain >= 0 ? '▲' : '▼' }} {{ abs($totalGainPercentage) }}%
                </p>
            </div>
        </div>

        <!-- Assets List -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-3">
            @forelse($assets as $asset)
                @php
                    $isProfit = $asset->unrealized_gain >= 0;
                    $assetJson = [
                        'id'                     => $asset->id,
                        'name'                   => $asset->name,
                        'asset_type'             => $asset->asset_type,
                        'unit_label'             => $asset->unit_label,
                        'quantity'               => (float) $asset->quantity,
                        'buy_price_per_unit'     => (float) $asset->buy_price_per_unit,
                        'current_price_per_unit' => (float) $asset->current_price_per_unit,
                        'notes'                  => $asset->notes ?? '',
                    ];
                @endphp
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-2xl border border-gray-100 hover:border-indigo-200 transition">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $asset->type_label }}
                            </span>
                            <h3 class="text-base font-bold text-gray-900">{{ $asset->name }}</h3>
                        </div>
                        <p class="text-xs text-gray-500">
                            Jumlah: <strong>{{ rtrim(rtrim(number_format((float)$asset->quantity, 4, ',', '.'), '0'), ',') }} {{ $asset->unit_label }}</strong>
                            • Harga Beli: Rp {{ number_format((float)$asset->buy_price_per_unit, 0, ',', '.') }}/{{ $asset->unit_label }}
                            • Harga Pasar: <strong class="text-indigo-600">Rp {{ number_format((float)$asset->current_price_per_unit, 0, ',', '.') }}/{{ $asset->unit_label }}</strong>
                        </p>
                        @if($asset->notes)
                            <p class="text-[11px] text-gray-400 italic">{{ $asset->notes }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center justify-between lg:justify-end gap-4 shrink-0">
                        <div class="text-left lg:text-right">
                            <p class="text-xs text-gray-400">Nilai Saat Ini</p>
                            <p class="text-lg font-extrabold text-gray-900">Rp {{ number_format($asset->current_value, 0, ',', '.') }}</p>
                            <p class="text-xs font-bold {{ $isProfit ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isProfit ? '+' : '-' }} Rp {{ number_format(abs($asset->unrealized_gain), 0, ',', '.') }} ({{ $isProfit ? '+' : '' }}{{ $asset->unrealized_gain_percentage }}%)
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick='openUpdatePriceModal(@json($assetJson))'
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition">
                                🔄 Update Harga
                            </button>
                            <button type="button" onclick='openEditAssetModal(@json($assetJson))'
                                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition">
                                Edit
                            </button>
                            <form action="{{ route('asset.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus aset ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus">✕</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 text-sm">
                    Belum ada aset investasi tercatat. Klik "+ Tambah Aset Baru" untuk mulai memantau nilai aset Anda (emas, reksadana, saham, valas).
                </div>
            @endforelse
        </div>
    </main>

    <!-- Modal Create / Edit Aset -->
    <div id="modal-asset" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="document.getElementById('modal-asset').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="asset-modal-title" class="text-lg font-bold text-gray-800">Tambah Aset Baru</h3>
                    <button type="button" onclick="document.getElementById('modal-asset').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form id="form-asset" action="{{ route('asset.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="asset-form-method" value="POST">

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Aset <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="asset-name" required placeholder="Contoh: Emas Antam / Bibit RDPU"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jenis Aset</label>
                            <select name="asset_type" id="asset-type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                                @foreach($assetTypes as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Satuan Unit <span class="text-red-500">*</span></label>
                            <input type="text" name="unit_label" id="asset-unit" required placeholder="gram / unit / lembar / USD"
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jumlah <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" id="asset-qty" min="0.0001" step="0.0001" required value="1"
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Beli/Unit</label>
                            <input type="number" name="buy_price_per_unit" id="asset-buy" min="0" step="0.01" required
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Pasar/Unit</label>
                            <input type="number" name="current_price_per_unit" id="asset-current" min="0" step="0.01" required
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Catatan (Opsional)</label>
                        <input type="text" name="notes" id="asset-notes" placeholder="Contoh: Simpan di brankas / akun Bibit"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('modal-asset').classList.add('hidden')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200">
                            Simpan Aset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update Harga Pasar -->
    <div id="modal-asset-price" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="document.getElementById('modal-asset-price').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block w-full max-w-sm p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Update Harga Pasar</h3>
                <p class="text-xs text-gray-500 mb-4" id="priceModalAssetName">-</p>

                <form id="form-asset-price" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Pasar Terbaru per <span id="priceModalUnit">unit</span> (Rp)</label>
                        <input type="number" name="current_price_per_unit" id="priceModalInput" min="0" step="0.01" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base font-bold">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-asset-price').classList.add('hidden')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold">
                            Update Harga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function openCreateAssetModal() {
        document.getElementById('asset-modal-title').textContent = 'Tambah Aset Baru';
        document.getElementById('form-asset').action = "{{ route('asset.store') }}";
        document.getElementById('asset-form-method').value = 'POST';
        document.getElementById('asset-name').value = '';
        document.getElementById('asset-type').value = 'gold';
        document.getElementById('asset-unit').value = 'gram';
        document.getElementById('asset-qty').value = 1;
        document.getElementById('asset-buy').value = '';
        document.getElementById('asset-current').value = '';
        document.getElementById('asset-notes').value = '';
        document.getElementById('modal-asset').classList.remove('hidden');
    }

    function openEditAssetModal(asset) {
        document.getElementById('asset-modal-title').textContent = 'Edit Aset';
        document.getElementById('form-asset').action = '/aset/' + asset.id;
        document.getElementById('asset-form-method').value = 'PUT';
        document.getElementById('asset-name').value = asset.name;
        document.getElementById('asset-type').value = asset.asset_type;
        document.getElementById('asset-unit').value = asset.unit_label;
        document.getElementById('asset-qty').value = asset.quantity;
        document.getElementById('asset-buy').value = asset.buy_price_per_unit;
        document.getElementById('asset-current').value = asset.current_price_per_unit;
        document.getElementById('asset-notes').value = asset.notes || '';
        document.getElementById('modal-asset').classList.remove('hidden');
    }

    function openUpdatePriceModal(asset) {
        document.getElementById('form-asset-price').action = '/aset/' + asset.id + '/price';
        document.getElementById('priceModalAssetName').textContent = asset.name;
        document.getElementById('priceModalUnit').textContent = asset.unit_label;
        document.getElementById('priceModalInput').value = asset.current_price_per_unit;
        document.getElementById('modal-asset-price').classList.remove('hidden');
    }
</script>
