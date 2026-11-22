<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Siklus Gajian (Payday-to-Payday Cycle)</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Tentukan tanggal gajian Anda agar filter transaksi, batas aman belanja harian, dan hitung mundur gajian mengikuti siklus P2P Anda.
                        </p>
                    </div>

                    @if(session('payday_status'))
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                            {{ session('payday_status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.payday.update') }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-wrap items-center gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Gajian (1–31)</label>
                                <input type="number" name="payday_day" min="1" max="31" required
                                       value="{{ old('payday_day', auth()->user()->payday_day ?? 1) }}"
                                       class="w-28 px-3 py-2 text-xs font-bold rounded-md border border-gray-300">
                            </div>
                            <label class="flex items-center gap-2 text-xs text-gray-700 pt-5">
                                <input type="checkbox" name="payday_weekend_adjust" value="1"
                                       {{ old('payday_weekend_adjust', auth()->user()->payday_weekend_adjust) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600">
                                <span>Geser ke hari Jumat jika jatuh di akhir pekan</span>
                            </label>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                            Simpan Tanggal Payday
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Telegram Bot Copilot</h3>
                            <p class="text-sm text-gray-600">Catat transaksi cepat dari chat Telegram dan terima pengingat harian otomatis.</p>
                        </div>
                        @if(auth()->user()->telegram_chat_id)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Terhubung</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">Belum Terhubung</span>
                        @endif
                    </div>

                    @if(session('telegram_status'))
                        <div class="p-3 rounded-lg bg-indigo-50 border border-indigo-200 text-xs font-semibold text-indigo-800">
                            {{ session('telegram_status') }}
                        </div>
                    @endif

                    @if(auth()->user()->telegram_chat_id)
                        <div class="flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('profile.telegram.toggle') }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 rounded-md text-xs font-bold uppercase tracking-widest border transition {{ auth()->user()->telegram_notifications_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200' }}">
                                    {{ auth()->user()->telegram_notifications_enabled ? 'Pengingat Harian: Aktif' : 'Pengingat Harian: Nonaktif' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('profile.telegram.unlink') }}" onsubmit="return confirm('Putuskan koneksi Telegram?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 rounded-md text-xs font-bold uppercase tracking-widest bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                                    Putuskan Koneksi
                                </button>
                            </form>
                        </div>
                    @else
                        @if(auth()->user()->telegram_link_code)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 text-xs space-y-1">
                                <p class="text-gray-600">Kirim perintah berikut ke <strong>@{{ config('services.telegram.bot_username') }}</strong>:</p>
                                <p class="font-mono font-bold text-indigo-700 text-sm">/link {{ auth()->user()->telegram_link_code }}</p>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('profile.telegram.code') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                                Buat Kode Tautan Telegram
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Backup & Restore Full Data (JSON)</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Unduh salinan lengkap data keuangan Anda atau pulihkan dari file backup JSON dengan preview aman.
                        </p>
                    </div>

                    @if(session('backup_status'))
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                            {{ session('backup_status') }}
                        </div>
                    @endif

                    @if(session('backup_error'))
                        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800">
                            {{ session('backup_error') }}
                        </div>
                    @endif

                    <div>
                        <a href="{{ route('profile.backup') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                            Download Backup JSON
                        </a>
                    </div>

                    <form method="POST" action="{{ route('profile.backup.preview') }}" enctype="multipart/form-data" class="pt-3 border-t border-gray-100 space-y-2.5">
                        @csrf
                        <label class="block text-xs font-bold text-gray-700">Pulihkan dari File Backup (.json)</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="file" name="backup_file" accept=".json,application/json" required class="text-xs text-gray-600">
                            <button type="submit" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold uppercase tracking-wider rounded-md transition">
                                Preview Restore
                            </button>
                        </div>
                    </form>

                    @if(session('backup_restore_summary'))
                        @php $bs = session('backup_restore_summary'); @endphp
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-3">
                            <p class="text-xs font-bold text-amber-900">Dry-Run Preview Backup (Exported: {{ $bs['exported_at'] ?? '-' }})</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-amber-900">
                                <div>Rekening: <strong>{{ $bs['savings'] ?? 0 }}</strong></div>
                                <div>Kategori: <strong>{{ $bs['categories'] ?? 0 }}</strong></div>
                                <div>Smart Rules: <strong>{{ $bs['category_rules'] ?? 0 }}</strong></div>
                                <div>Transaksi: <strong>{{ $bs['transactions'] ?? 0 }}</strong></div>
                                <div>Aset: <strong>{{ $bs['assets'] ?? 0 }}</strong></div>
                                <div>Hutang: <strong>{{ $bs['debts'] ?? 0 }}</strong></div>
                                <div>Kartu Kredit: <strong>{{ $bs['credit_cards'] ?? 0 }}</strong></div>
                            </div>
                            <form method="POST" action="{{ route('profile.backup.restore') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                                    Konfirmasi Pulihkan Data
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Kunci PIN Cepat (PWA / Perangkat Ini)</h3>
                            <p class="text-sm text-gray-600">Tambahkan PIN 4–6 digit untuk mengunci tampilan Finansia saat dibuka di HP atau browser ini.</p>
                        </div>
                        <span id="pin-status-badge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">Nonaktif</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <input type="password" id="profile-pin-input" inputmode="numeric" maxlength="6" placeholder="PIN baru (4-6 digit)"
                               class="px-3 py-2 text-xs rounded-md border border-gray-300 w-44">
                        <button type="button" onclick="saveQuickPin()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                            Simpan PIN
                        </button>
                        <button type="button" id="btn-lock-now" onclick="lockAppWithPin()" class="hidden px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-widest rounded-md transition">
                            Kunci Sekarang
                        </button>
                        <button type="button" id="btn-remove-pin" onclick="removeQuickPin()" class="hidden px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold uppercase tracking-widest rounded-md transition">
                            Hapus PIN
                        </button>
                    </div>
                    <p id="pin-feedback" class="hidden text-xs font-semibold text-indigo-700"></p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
