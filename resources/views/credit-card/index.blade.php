<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">💳 Kartu Kredit & Cicilan</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Pantau limit kartu kredit / PayLater, siklus tagihan, dan progres cicilan bulanan Anda</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <!-- KPI Ringkasan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Total Limit</p>
                <p class="text-xl font-extrabold text-gray-800 mt-1">Rp {{ number_format($totalLimit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Total Limit Terpakai</p>
                <p class="text-xl font-extrabold text-rose-600 mt-1">Rp {{ number_format($totalUsed, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Sisa Limit Tersedia</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($totalAvailable, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Estimasi Tagihan Bulan Ini</p>
                <p class="text-xl font-extrabold text-indigo-700 mt-1">Rp {{ number_format($totalEstimatedStatement, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Form Tambah Kartu / PayLater -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h2 class="text-sm font-bold text-gray-800 mb-3">+ Tambah Kartu Kredit / PayLater</h2>
            <form method="POST" action="{{ route('credit-card.store') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Kartu / PayLater</label>
                    <input type="text" name="name" required placeholder="Contoh: BCA Everyday / SPayLater"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Limit (Rp)</label>
                    <input type="number" name="credit_limit" required min="0" step="100000" placeholder="10000000"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Tagihan Reguler (Rp)</label>
                    <input type="number" name="current_balance" min="0" step="10000" value="0"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Tgl Cetak</label>
                    <input type="number" name="billing_day" required min="1" max="28" value="1"
                           class="w-full px-2.5 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Jatuh Tempo</label>
                    <input type="number" name="due_day" required min="1" max="28" value="15"
                           class="w-full px-2.5 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">4 Digit</label>
                    <input type="text" name="last_four" maxlength="4" placeholder="1234"
                           class="w-full px-2.5 py-2 text-xs rounded-xl border border-gray-200">
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                        Simpan Kartu
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Kartu & Cicilan -->
        <div class="space-y-5">
            @forelse($cards as $card)
                @php
                    $util = $card->utilization_percentage;
                    $barColor = $util > 70 ? 'bg-rose-500' : ($util >= 30 ? 'bg-amber-500' : 'bg-emerald-500');
                @endphp
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-800">{{ $card->name }}</h3>
                                @if($card->last_four)
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-[11px] font-mono">•••• {{ $card->last_four }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Cetak tagihan: Tgl {{ $card->billing_day }} • Jatuh tempo: Tgl {{ $card->due_day }} • Estimasi tagihan bulan ini:
                                <strong class="text-indigo-700">Rp {{ number_format($card->estimated_statement, 0, ',', '.') }}</strong>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('credit-card.balance', $card) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="current_balance" value="{{ (int) $card->current_balance }}" min="0" step="10000"
                                       class="w-32 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200" title="Tagihan Reguler Non-Cicilan">
                                <button type="submit" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-bold rounded-lg">
                                    Update Tagihan
                                </button>
                            </form>
                            <form method="POST" action="{{ route('credit-card.destroy', $card) }}" onsubmit="return confirm('Hapus kartu beserta semua cicilannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold rounded-lg">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Utilization Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-500">Pemakaian Limit: <strong>Rp {{ number_format($card->total_used, 0, ',', '.') }}</strong> / Rp {{ number_format($card->credit_limit, 0, ',', '.') }}</span>
                            <span class="font-bold {{ $util > 70 ? 'text-rose-600' : 'text-gray-700' }}">{{ $util }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $barColor }}" style="width: {{ min(100, $util) }}%"></div>
                        </div>
                    </div>

                    <!-- Tambah Cicilan -->
                    <form method="POST" action="{{ route('credit-card.installments.store', $card) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
                        @csrf
                        <input type="text" name="title" required placeholder="Nama barang / cicilan (misal: Laptop)" class="sm:col-span-4 px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white">
                        <input type="number" name="total_amount" required min="1" step="10000" placeholder="Total Harga (Rp)" class="sm:col-span-3 px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white">
                        <input type="number" name="total_months" required min="1" max="120" placeholder="Tenor (bln)" class="sm:col-span-2 px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white">
                        <input type="number" name="paid_months" min="0" value="0" placeholder="Sudah bayar" class="sm:col-span-1 px-2.5 py-2 text-xs rounded-xl border border-gray-200 bg-white" title="Jumlah bulan yang sudah dibayar">
                        <button type="submit" class="sm:col-span-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl">
                            + Cicilan
                        </button>
                    </form>

                    <!-- List Cicilan -->
                    @if($card->installments->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($card->installments as $ins)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl border {{ $ins->status === 'paid' ? 'bg-emerald-50/40 border-emerald-100' : 'bg-white border-gray-100' }}">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-bold text-gray-800">{{ $ins->title }}</p>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ins->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                                {{ $ins->paid_months }} / {{ $ins->total_months }} bln
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            Cicilan: <strong>Rp {{ number_format($ins->monthly_amount, 0, ',', '.') }}/bln</strong> • Sisa: Rp {{ number_format($ins->remaining_amount, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($ins->status === 'active')
                                            <form method="POST" action="{{ route('credit-card.installments.pay', $ins) }}" class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="savings_id" class="text-[11px] px-2 py-1.5 rounded-lg border border-gray-200 bg-white">
                                                    <option value="">Tandai bayar saja</option>
                                                    @foreach($savings as $s)
                                                        <option value="{{ $s->id }}">Potong {{ $s->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg">
                                                    Bayar 1 Bln
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold text-emerald-700">✓ Lunas</span>
                                        @endif

                                        <form method="POST" action="{{ route('credit-card.installments.destroy', $ins) }}" onsubmit="return confirm('Hapus cicilan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-1.5">✕</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-gray-200 p-10 text-center">
                    <p class="text-sm font-bold text-gray-600">Belum ada kartu kredit atau PayLater terdaftar.</p>
                    <p class="text-xs text-gray-400 mt-1">Tambahkan kartu di formulir atas untuk memantau limit dan cicilan bulanan Anda.</p>
                </div>
            @endforelse
        </div>
    </main>
</x-app-layout>
