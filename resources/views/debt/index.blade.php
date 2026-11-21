<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🧾 Hutang, Piutang & Cicilan</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Catat pinjaman yang harus dibayar (Hutang) maupun uang yang dipinjam orang (Piutang)</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-debt').classList.remove('hidden')"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 transition-all">
                + Catat Hutang / Piutang
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
            <div class="bg-rose-50/70 rounded-2xl border border-rose-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-rose-700 uppercase">Sisa Hutang Saya (Kewajiban)</p>
                <p class="text-2xl font-extrabold text-rose-700 mt-1">Rp {{ number_format($totalPayableRemaining, 0, ',', '.') }}</p>
            </div>
            <div class="bg-emerald-50/70 rounded-2xl border border-emerald-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-emerald-700 uppercase">Sisa Piutang (Akan Diterima)</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalReceivableRemaining, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <p class="text-xs font-bold text-gray-500 uppercase">Melewati Jatuh Tempo</p>
                <p class="text-2xl font-extrabold {{ $overdueCount > 0 ? 'text-rose-600' : 'text-gray-800' }} mt-1">{{ $overdueCount }} Catatan</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2">
            <a href="{{ route('debt.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ empty($typeFilter) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                Semua
            </a>
            <a href="{{ route('debt.index', ['type' => 'payable']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $typeFilter === 'payable' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                Hutang Saya
            </a>
            <a href="{{ route('debt.index', ['type' => 'receivable']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $typeFilter === 'receivable' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                Piutang (Orang Pinjam)
            </a>
        </div>

        <!-- Debt List -->
        <div class="space-y-3">
            @forelse($debts as $debt)
                <div class="bg-white rounded-2xl border {{ $debt->is_overdue ? 'border-rose-200' : 'border-gray-100' }} p-5 shadow-2xs space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $debt->type === 'payable' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $debt->type === 'payable' ? 'Hutang Saya' : 'Piutang' }}
                                </span>
                                <h3 class="text-base font-bold text-gray-900">{{ $debt->person_name }}</h3>
                                @if($debt->status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">✓ Lunas</span>
                                @elseif($debt->is_overdue)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">⏰ Jatuh Tempo</span>
                                @endif
                            </div>
                            @if($debt->description)
                                <p class="text-xs text-gray-500 mt-1">{{ $debt->description }}</p>
                            @endif
                            @if($debt->due_date)
                                <p class="text-[11px] text-gray-400 mt-0.5">Jatuh tempo: {{ $debt->due_date->format('d M Y') }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Sisa / Total</p>
                                <p class="text-base font-extrabold text-gray-900">
                                    Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-gray-400">/ Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</span>
                                </p>
                            </div>

                            @if($debt->status !== 'paid')
                                @php
                                    $payPayload = [
                                        'id' => $debt->id,
                                        'person_name' => $debt->person_name,
                                        'type' => $debt->type,
                                        'remaining' => $debt->remaining_amount,
                                    ];
                                @endphp
                                <button type="button" onclick='openPayDebtModal(@json($payPayload))'
                                        class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    {{ $debt->type === 'payable' ? 'Bayar Cicilan' : 'Terima Bayaran' }}
                                </button>
                            @endif

                            <form action="{{ route('debt.destroy', $debt) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus">✕</button>
                            </form>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full {{ $debt->status === 'paid' ? 'bg-emerald-500' : 'bg-indigo-600' }}" style="width: {{ $debt->progress_percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                            <span>Terbayar: Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</span>
                            <span class="font-bold text-indigo-600">{{ $debt->progress_percentage }}%</span>
                        </div>
                    </div>

                    @if($debt->payments->isNotEmpty())
                        <details class="text-xs pt-1">
                            <summary class="cursor-pointer font-semibold text-gray-500 hover:text-indigo-600 select-none">
                                Riwayat Pembayaran ({{ $debt->payments->count() }})
                            </summary>
                            <div class="mt-2 space-y-1.5">
                                @foreach($debt->payments as $pay)
                                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 border border-gray-100">
                                        <span>
                                            <strong>{{ $pay->paid_at->format('d M Y') }}</strong>
                                            @if($pay->savingAccount)
                                                • via {{ $pay->savingAccount->name }}
                                            @endif
                                            @if($pay->notes)
                                                — {{ $pay->notes }}
                                            @endif
                                        </span>
                                        <span class="font-bold text-gray-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center text-gray-400 text-sm">
                    Belum ada catatan hutang atau piutang.
                </div>
            @endforelse
        </div>
    </main>

    <!-- Modal Catat Hutang/Piutang -->
    <div id="modal-debt" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="document.getElementById('modal-debt').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Catat Hutang / Piutang Baru</h3>
                    <button type="button" onclick="document.getElementById('modal-debt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form action="{{ route('debt.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jenis</label>
                        <select name="type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                            <option value="payable">Hutang Saya (Harus Saya Bayar)</option>
                            <option value="receivable">Piutang (Orang Lain Pinjam ke Saya)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Pihak / Orang / Lembaga <span class="text-red-500">*</span></label>
                        <input type="text" name="person_name" required placeholder="Contoh: Budi / KPR Bank"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Total Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="total_amount" min="1000" step="1000" required placeholder="1000000"
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jatuh Tempo (Opsional)</label>
                            <input type="date" name="due_date" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Keterangan</label>
                        <input type="text" name="description" placeholder="Contoh: Pinjaman modal / talangan makan"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('modal-debt').classList.add('hidden')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200">
                            Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bayar / Terima Cicilan -->
    <div id="modal-pay-debt" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="document.getElementById('modal-pay-debt').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" id="payDebtModalTitle">Bayar Cicilan</h3>
                        <p class="text-xs text-gray-500" id="payDebtModalSubtitle">-</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-pay-debt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form id="form-pay-debt" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" id="payDebtAmountInput" min="1" step="0.01" required
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tanggal Bayar</label>
                            <input type="date" name="paid_at" value="{{ now()->toDateString() }}"
                                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Mutasikan ke Rekening (Opsional)</label>
                        <select name="savings_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                            <option value="">-- Tidak Perlu Mutasi Rekening --</option>
                            @foreach($savings as $sav)
                                <option value="{{ $sav->id }}">{{ $sav->name }} (Saldo: Rp {{ number_format($sav->current_balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Contoh: Cicilan ke-1"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('modal-pay-debt').classList.add('hidden')" class="flex-1 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-200">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function openPayDebtModal(debt) {
        document.getElementById('form-pay-debt').action = '/hutang-piutang/' + debt.id + '/pay';
        document.getElementById('payDebtModalTitle').textContent = debt.type === 'payable' ? 'Bayar Hutang ke ' + debt.person_name : 'Terima Piutang dari ' + debt.person_name;
        document.getElementById('payDebtModalSubtitle').textContent = 'Sisa belum lunas: Rp ' + Number(debt.remaining).toLocaleString('id-ID');
        document.getElementById('payDebtAmountInput').value = debt.remaining;
        document.getElementById('payDebtAmountInput').max = debt.remaining;
        document.getElementById('modal-pay-debt').classList.remove('hidden');
    }
</script>
