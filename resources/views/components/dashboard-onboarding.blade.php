@props(['onboarding'])

@if(!empty($onboarding['show']))
    @php
        $done = (int) ($onboarding['completed_steps'] ?? 0);
        $pct = round(($done / 3) * 100);
    @endphp
    <div class="mb-6 bg-gradient-to-r from-indigo-50 via-white to-sky-50 border border-indigo-200 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Panduan Mulai Cepat ({{ $done }}/3 Selesai)</p>
                <h3 class="text-base sm:text-lg font-bold text-gray-800">Siapkan Finansia dalam 3 Langkah Mudah</h3>
            </div>
            <div class="w-full sm:w-44">
                <div class="w-full h-2 bg-indigo-100 rounded-full overflow-hidden">
                    <div class="h-2 bg-indigo-600 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ route('saving.index') }}" class="flex items-start gap-3 p-3.5 rounded-xl border transition {{ ($onboarding['has_saving'] ?? false) ? 'bg-emerald-50/70 border-emerald-200' : 'bg-white border-gray-200 hover:border-indigo-300' }}">
                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ ($onboarding['has_saving'] ?? false) ? 'bg-emerald-500 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ ($onboarding['has_saving'] ?? false) ? '✓' : '1' }}
                </span>
                <div>
                    <p class="text-xs font-bold text-gray-800">Buat Rekening / Dompet</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Tambahkan rekening bank atau dompet tunai pertama Anda.</p>
                </div>
            </a>

            <a href="{{ route('budget.index') }}" class="flex items-start gap-3 p-3.5 rounded-xl border transition {{ ($onboarding['has_budget'] ?? false) ? 'bg-emerald-50/70 border-emerald-200' : 'bg-white border-gray-200 hover:border-indigo-300' }}">
                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ ($onboarding['has_budget'] ?? false) ? 'bg-emerald-500 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ ($onboarding['has_budget'] ?? false) ? '✓' : '2' }}
                </span>
                <div>
                    <p class="text-xs font-bold text-gray-800">Pasang Anggaran Bulanan</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Atur batas belanja per pos untuk mengaktifkan Daily Safe-to-Spend.</p>
                </div>
            </a>

            <a href="{{ route('transaction.index', ['add' => 1]) }}" class="flex items-start gap-3 p-3.5 rounded-xl border transition {{ ($onboarding['has_transaction'] ?? false) ? 'bg-emerald-50/70 border-emerald-200' : 'bg-white border-gray-200 hover:border-indigo-300' }}">
                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ ($onboarding['has_transaction'] ?? false) ? 'bg-emerald-500 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ ($onboarding['has_transaction'] ?? false) ? '✓' : '3' }}
                </span>
                <div>
                    <p class="text-xs font-bold text-gray-800">Catat Transaksi Pertama</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Catat manual, import mutasi PDF, atau pakai AI Copilot.</p>
                </div>
            </a>
        </div>
    </div>
@endif
