<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🤝 Dompet Bersama (Shared Wallet)</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Bagikan rekening tertentu dengan pasangan atau keluarga tanpa mencampur rekening pribadi Anda</p>
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

        <!-- Rekening Milik Saya yang Bisa Dibagikan -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-lg font-bold text-gray-800">Rekening Saya (Kelola Akses Bersama)</h2>
                <p class="text-xs text-gray-500">Undang anggota dengan email terdaftar sebagai <strong>Editor</strong> (bisa catat transaksi) atau <strong>Viewer</strong> (hanya lihat)</p>
            </div>

            @forelse($ownedSavings as $saving)
                <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">{{ $saving->name }}</h3>
                            <p class="text-xs text-gray-500">Saldo: <strong class="text-gray-800">Rp {{ number_format((float) $saving->current_balance, 0, ',', '.') }}</strong></p>
                        </div>

                        <form method="POST" action="{{ route('shared-wallet.members.store', $saving) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="email" name="email" required placeholder="Email pasangan / anggota"
                                   class="px-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-white">
                            <select name="role" class="px-2.5 py-1.5 text-xs rounded-xl border border-gray-200 bg-white">
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            <button type="submit" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl">
                                + Undang
                            </button>
                        </form>
                    </div>

                    @if($saving->members->isNotEmpty())
                        <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200/70">
                            @foreach($saving->members as $member)
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-gray-200 text-xs">
                                    <span class="font-bold text-gray-700">{{ $member->name }}</span>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase">{{ $member->pivot->role }}</span>
                                    <form method="POST" action="{{ route('shared-wallet.members.destroy', [$saving, $member]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold" title="Cabut akses">✕</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-6">Anda belum memiliki rekening tabungan. Buat rekening dulu di menu Saving & Rekening.</p>
            @endforelse
        </div>

        <!-- Dompet yang Dibagikan ke Saya -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-lg font-bold text-gray-800">Dompet Dibagikan ke Saya ({{ $sharedWithMe->count() }})</h2>
                <p class="text-xs text-gray-500">Rekening milik pasangan/keluarga yang dibagikan kepada akun Anda</p>
            </div>

            @forelse($sharedWithMe as $shared)
                @php
                    $myPivot = $shared->members->firstWhere('id', auth()->id())?->pivot;
                    $myRole = $myPivot?->role ?? 'viewer';
                @endphp
                <div class="p-4 rounded-2xl border border-indigo-100 bg-indigo-50/20 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-gray-800">{{ $shared->name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-indigo-100 text-indigo-700">{{ $myRole }}</span>
                            </div>
                            <p class="text-xs text-gray-500">Pemilik: <strong>{{ $shared->user?->name }}</strong> • Saldo: <strong class="text-indigo-800">Rp {{ number_format((float) $shared->current_balance, 0, ',', '.') }}</strong></p>
                        </div>
                    </div>

                    @if($myRole === 'editor')
                        <form method="POST" action="{{ route('shared-wallet.transactions.store', $shared) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 bg-white p-3 rounded-xl border border-gray-200">
                            @csrf
                            <select name="type" class="sm:col-span-2 px-2.5 py-2 text-xs rounded-lg border border-gray-200">
                                <option value="Cash Out">Pengeluaran</option>
                                <option value="Cash In">Pemasukan</option>
                            </select>
                            <input type="text" name="description" required placeholder="Keterangan transaksi bersama" class="sm:col-span-4 px-3 py-2 text-xs rounded-lg border border-gray-200">
                            <input type="number" name="amount" required min="1" step="1000" placeholder="Nominal (Rp)" class="sm:col-span-2 px-3 py-2 text-xs rounded-lg border border-gray-200">
                            <select name="category_id" class="sm:col-span-2 px-2.5 py-2 text-xs rounded-lg border border-gray-200">
                                <option value="">Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="sm:col-span-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                                Catat
                            </button>
                        </form>
                    @endif

                    @if($shared->transaction->isNotEmpty())
                        <div class="space-y-1.5">
                            <p class="text-[11px] font-bold text-gray-500 uppercase">Transaksi Terakhir</p>
                            @foreach($shared->transaction as $tx)
                                <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg bg-white border border-gray-100">
                                    <span class="font-semibold text-gray-700 truncate">{{ $tx->description }}</span>
                                    <span class="font-bold shrink-0 {{ $tx->type === 'Cash In' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $tx->type === 'Cash In' ? '+' : '-' }} Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-6">Belum ada dompet yang dibagikan ke akun Anda.</p>
            @endforelse
        </div>
    </main>
</x-app-layout>
