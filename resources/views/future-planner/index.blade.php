<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>🎯</span>
                    <span>{{ __('Future Planner') }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Rencanakan target pembelian, proyek, atau impian masa depan beserta breakdown item kebutuhannya.
                </p>
            </div>
            <div>
                <button type="button" onclick="openPlanModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Buat Rencana Baru</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- KPI Ringkasan Kartu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Rencana</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            📋
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalPlansCount) }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $completedPlansCount }} rencana telah selesai</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Estimasi Kebutuhan</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                            💰
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($grandTotalEstimated, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">Akumulasi seluruh item rencana</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Terbeli / Terealisasi</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            ✅
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($grandTotalCompleted, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">Item yang telah dicentang terbeli</div>
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
                        <div class="text-2xl font-bold text-amber-600">Rp {{ number_format($grandTotalRemaining, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">Dana yang masih perlu disiapkan</div>
                    </div>
                </div>
            </div>

            <!-- Filter Status & Header List -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <a href="{{ route('future-planner.index') }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                        Semua Rencana
                    </a>
                    <a href="{{ route('future-planner.index', ['status' => 'planned']) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'planned' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                        Direncanakan
                    </a>
                    <a href="{{ route('future-planner.index', ['status' => 'in_progress']) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'in_progress' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                        Sedang Berjalan
                    </a>
                    <a href="{{ route('future-planner.index', ['status' => 'completed']) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'completed' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                        Selesai
                    </a>
                </div>
            </div>

            <!-- Grid Kartu Future Plan -->
            @if($plans->isEmpty())
                <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-3xl">
                        🚀
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Belum Ada Rencana Masa Depan</h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto mt-1 mb-6">
                        Buat rencana pertama Anda seperti "Setup Ruang Kerja", "Pernikahan", atau "Liburan", lalu rincikan item kebutuhan dan harganya di dalamnya.
                    </p>
                    <button type="button" onclick="openPlanModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Buat Rencana Sekarang</span>
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($plans as $plan)
                        @php
                            $targetAmount = $plan->total_estimated_amount;
                            $currentAmount = $plan->total_completed_amount;
                            $progress = $plan->progress_percentage;
                            $itemsCount = $plan->total_items_count;
                            $completedCount = $plan->completed_items_count;
                        @endphp
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $plan->status_badge_class }}">
                                        {{ $plan->status_label }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="openEditPlanModal({{ json_encode($plan) }})" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition" title="Edit Rencana">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('future-planner.destroy', $plan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana &quot;{{ $plan->title }}&quot; beserta seluruh item di dalamnya?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Hapus Rencana">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition">
                                    <a href="{{ route('future-planner.show', $plan) }}">
                                        {{ $plan->title }}
                                    </a>
                                </h3>

                                @if($plan->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $plan->description }}</p>
                                @endif

                                @if($plan->target_date)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-3 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Target: {{ $plan->target_date->translatedFormat('d M Y') }}</span>
                                    </div>
                                @endif

                                <!-- Progress Bar & Nominal -->
                                <div class="mt-5 space-y-2">
                                    <div class="flex justify-between items-baseline text-xs">
                                        <span class="text-gray-500">Estimasi Kebutuhan</span>
                                        <span class="font-bold text-gray-900">Rp {{ number_format($targetAmount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px] text-gray-400">
                                        <span>Terbeli: Rp {{ number_format($currentAmount, 0, ',', '.') }}</span>
                                        <span class="font-semibold text-indigo-600">{{ $progress }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Card -->
                            <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500">
                                    {{ $itemsCount }} item <span class="text-gray-400">({{ $completedCount }} selesai)</span>
                                </span>
                                <a href="{{ route('future-planner.show', $plan) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    <span>Detail & Item</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $plans->links() }}
                </div>
            @endif

        </div>
    </div>

    <!-- Modal Buat Rencana Baru -->
    <div id="planModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span>✨</span>
                    <span>Buat Future Plan Baru</span>
                </h3>
                <button type="button" onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
            </div>

            <form action="{{ route('future-planner.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Rencana / Target <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Setup Ruang Kerja Minimalis" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Deskripsi / Catatan Rencana</label>
                    <textarea name="description" rows="3" placeholder="Rencana pengadaan meja, kursi ergonomis, monitor, dan aksesoris pendukung kerja..." class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-400"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Target Tanggal Selesai</label>
                        <input type="date" name="target_date" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Status Awal</label>
                        <select name="status" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="planned">Direncanakan</option>
                            <option value="in_progress">Sedang Berjalan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition">
                        Simpan & Mulai Tambah Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Rencana -->
    <div id="editPlanModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Rencana</h3>
                <button type="button" onclick="closeEditPlanModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
            </div>

            <form id="editPlanForm" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Rencana <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit_title" name="title" required class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Deskripsi</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Target Tanggal</label>
                        <input type="date" id="edit_target_date" name="target_date" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                        <select id="edit_status" name="status" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="planned">Direncanakan</option>
                            <option value="in_progress">Sedang Berjalan</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeEditPlanModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition">
                        Perbarui Rencana
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPlanModal() {
            document.getElementById('planModal').classList.remove('hidden');
        }

        function closePlanModal() {
            document.getElementById('planModal').classList.add('hidden');
        }

        function openEditPlanModal(plan) {
            const form = document.getElementById('editPlanForm');
            form.action = `/future-planner/${plan.id}`;
            document.getElementById('edit_title').value = plan.title || '';
            document.getElementById('edit_description').value = plan.description || '';
            document.getElementById('edit_target_date').value = plan.target_date ? plan.target_date.substring(0, 10) : '';
            document.getElementById('edit_status').value = plan.status || 'planned';
            document.getElementById('editPlanModal').classList.remove('hidden');
        }

        function closeEditPlanModal() {
            document.getElementById('editPlanModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
