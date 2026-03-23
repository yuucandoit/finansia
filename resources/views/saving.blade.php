<x-app-layout>
    <div class="py-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-slide-down">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <p class="text-green-800 font-semibold flex-1">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">💰 Tabungan Saya</h1>
                    <p class="text-gray-500 mt-1">Kelola rekening tabungan Anda</p>
                </div>
                <button onclick="openModalCreate()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Tabungan
                </button>
            </div>

            <!-- Total Balance Card -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl shadow-2xl p-8 text-white mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24"></div>
                
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium mb-2">Total Saldo Tabungan</p>
                    <h2 class="text-5xl font-extrabold mb-4 flex items-center">
                        <span class="balance-amount">
                                Rp ••••••••
                        </span>
                        <span class="showing-btn w-6 h-6 ml-3 text-white/70 cursor-pointer" title="Tampilkan/Sembunyikan Saldo">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                    </h2>
                    <p class="text-blue-100 text-sm">{{ count($savings) }} Rekening Tabungan Aktif</p>
                </div>
            </div>

            <!-- Savings Grid -->
            @if(count($savings) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($savings as $saving)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all p-6 border border-gray-100 group">
                            <!-- Bank Info -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">{{ $saving->bank?->name ?? 'Unknown Bank' }}</p>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $saving->name }}</h3>
                                    <p class="text-xs text-gray-400 font-mono mt-1">{{ $saving->account_number }}</p>
                                </div>
                                <div class="flex-">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center transition-colors cursor-default" onclick="detailSaving({{ $saving->id }})" title="Detail Tabungan">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l82.7 0-201.4 201.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3 448 192c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160c0-17.7-14.3-32-32-32L320 0zM80 96C35.8 96 0 131.8 0 176L0 432c0 44.2 35.8 80 80 80l256 0c44.2 0 80-35.8 80-80l0-80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 80c0 8.8-7.2 16-16 16L80 448c-8.8 0-16-7.2-16-16l0-256c0-8.8 7.2-16 16-16l80 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 96z"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">Saldo</p>
                                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($saving->current_balance, 0, ',', '.') }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 pt-4 border-t border-gray-100">
                                <button onclick="openModalEdit({{ $saving }})" class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    Edit
                                </button>
                                <button onclick="confirmDelete({{ $saving->id }}, '{{ $saving->name }}')" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Tabungan</h3>
                    <p class="text-gray-500 mb-6">Mulai mengelola keuangan Anda dengan menambahkan tabungan pertama</p>
                    <button onclick="openModalCreate()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all">
                        + Tambah Tabungan Pertama
                    </button>
                </div>
            @endif

        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div id="modal-saving" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 id="modal-title" class="text-2xl font-bold text-gray-800">Tambah Tabungan</h3>
                        <p class="text-sm text-gray-500 mt-1">Tambah rekening tabungan baru</p>
                    </div>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="form-saving" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Tabungan <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required placeholder="Contoh: Tabungan Utama BCA" 
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bank <span class="text-red-500">*</span></label>
                        <select name="bank_id" id="bank_id" required class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all select2_bank">
                            <option value="">Pilih Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" id="account_number" required placeholder="Contoh: 1234567890" 
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Saldo Awal <span class="text-red-500">*</span></label>
                        <input type="number" name="current_balance" id="current_balance" required min="0" step="0.01" placeholder="0" 
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border transition-all">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeModal()" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-5 rounded-xl font-semibold transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-5 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all">
                            💾 Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="modal-delete" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block w-full max-w-md p-8 my-8 overflow-hidden text-center align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Tabungan?</h3>
                <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus <strong id="delete-name"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                
                <form id="form-delete" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-5 rounded-xl font-semibold transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-5 rounded-xl font-bold shadow-lg shadow-red-200 transition-all">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes slide-down {
            from { 
                opacity: 0;
                transform: translateY(-20px); 
            }
            to { 
                opacity: 1;
                transform: translateY(0); 
            }
        }
        .animate-slide-down {
            animation: slide-down 0.3s ease-out;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
             // function showing eye toggle
            document.querySelectorAll('.showing-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const balanceAmount = this.previousElementSibling;
                    if (balanceAmount.textContent.includes('••••••••')) {
                            // Show the balance
                        balanceAmount.textContent = 'Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}';
                    } else {
                        // Hide the balance
                        balanceAmount.textContent = 'Rp ••••••••';
                    }
                });
            });
        });
        function openModalCreate() {
            document.getElementById('modal-title').textContent = 'Tambah Tabungan';
            document.getElementById('form-saving').action = '{{ route("saving.store") }}';
            document.getElementById('form-method').value = 'POST';
            document.getElementById('name').value = '';
            document.getElementById('bank_id').value = '';
            document.getElementById('account_number').value = '';
            document.getElementById('current_balance').value = '';
            document.getElementById('modal-saving').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function openModalEdit(saving) {
            document.getElementById('modal-title').textContent = 'Edit Tabungan';
            document.getElementById('form-saving').action = `/saving/${saving.id}`;
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('name').value = saving.name;
            document.getElementById('bank_id').value = saving.bank_id;
            document.getElementById('account_number').value = saving.account_number;
            document.getElementById('current_balance').value = saving.current_balance;
            document.getElementById('modal-saving').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modal-saving').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmDelete(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('form-delete').action = `/saving/${id}`;
            document.getElementById('modal-delete').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('modal-delete').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function detailSaving(id) {
            let url = `{{ route('saving.detail',':id') }}`;
            url = url.replace(":id",id)
            window.location.href = url;
        }

        // Close modal with ESC key
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });
    </script>
    {{-- Select2 --}}
    @push('scripts')
        <script type="module">
            $(document).ready(function() {
                $('.select2_bank').select2({
                    placeholder: 'Pilih bank',
                    allowClear: true
                });
            });
        </script>
    @endpush
</x-app-layout>
