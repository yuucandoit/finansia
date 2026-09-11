<x-app-layout>
    <div class="py-8 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                        Otoritas Master Mind
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#234066]">
                        Persetujuan Pendaftaran Akun
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Kelola dan verifikasi akun baru sebelum mereka diizinkan mengakses aplikasi Finansia.
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1566da] to-[#4892ef] text-white flex items-center justify-center font-bold text-xs">
                        MM
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-gray-800">wahyusnjy</p>
                        <p class="text-[10px] text-emerald-600 font-semibold">Master Mind Active</p>
                    </div>
                </div>
            </div>

            
            @if (session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['total'] }}</div>
                    <span class="text-[11px] text-gray-400">Semua pendaftar</span>
                </div>

                
                <div class="bg-white p-5 rounded-2xl border {{ $stats['pending'] > 0 ? 'border-amber-300 ring-2 ring-amber-100 bg-amber-50/20' : 'border-gray-100' }} shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Menunggu Approval</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-amber-800 mt-2">{{ $stats['pending'] }}</div>
                    <span class="text-[11px] {{ $stats['pending'] > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                        {{ $stats['pending'] > 0 ? 'Perlu tindakan segera' : 'Tidak ada antrean' }}
                    </span>
                </div>

                
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Disetujui</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['approved'] }}</div>
                    <span class="text-[11px] text-gray-400">Akun aktif login</span>
                </div>

                
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Ditolak</span>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['rejected'] }}</div>
                    <span class="text-[11px] text-gray-400">Akses diblokir</span>
                </div>
            </div>

            
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                
                <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
                    <a href="{{ route('admin.approvals.index', ['status' => 'all', 'search' => $search]) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition {{ $statusFilter === 'all' ? 'bg-[#234066] text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua ({{ $stats['total'] }})
                    </a>
                    <a href="{{ route('admin.approvals.index', ['status' => 'pending', 'search' => $search]) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                        <span>Pending</span>
                        @if($stats['pending'] > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'pending' ? 'bg-white text-amber-600' : 'bg-amber-200 text-amber-800' }}">
                                {{ $stats['pending'] }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.approvals.index', ['status' => 'approved', 'search' => $search]) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                        Disetujui ({{ $stats['approved'] }})
                    </a>
                    <a href="{{ route('admin.approvals.index', ['status' => 'rejected', 'search' => $search]) }}"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                        Ditolak ({{ $stats['rejected'] }})
                    </a>
                </div>

                
                <form method="GET" action="{{ route('admin.approvals.index') }}" class="w-full md:w-80 flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @if($search)
                        <a href="{{ route('admin.approvals.index', ['status' => $statusFilter]) }}" class="px-2.5 py-2 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="px-3.5 py-2 text-xs font-semibold bg-[#1566da] text-white rounded-xl hover:bg-[#1359c2] transition">
                        Cari
                    </button>
                </form>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3.5 px-6">Pengguna</th>
                                <th class="py-3.5 px-4">Role</th>
                                <th class="py-3.5 px-4">Status Approval</th>
                                <th class="py-3.5 px-4">Tanggal Daftar</th>
                                <th class="py-3.5 px-6 text-right">Aksi Master Mind</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50/60 transition {{ $user->isPending() ? 'bg-amber-50/20' : '' }}">
                                    
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $user->isMasterMind() ? 'bg-indigo-100 text-indigo-700 ring-2 ring-indigo-200' : 'bg-gray-100 text-gray-600' }}">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if($user->isMasterMind())
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                                            Master Mind
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $user->role === 'admin' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>

                                    
                                    <td class="py-4 px-4">
                                        @if($user->isPending())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu Approval
                                            </span>
                                        @elseif($user->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Disetujui
                                            </span>
                                        @elseif($user->isRejected())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>

                                    
                                    <td class="py-4 px-4 text-xs text-gray-500">
                                        <div>{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $user->created_at ? $user->created_at->format('H:i') . ' WIB' : '' }}</div>
                                    </td>

                                    
                                    <td class="py-4 px-6 text-right">
                                        @if($user->isMasterMind())
                                            <span class="text-xs text-gray-400 italic">Owner Tetap</span>
                                        @else
                                            <div class="flex items-center justify-end gap-2">
                                                
                                                @if($user->isPending() || $user->isRejected())
                                                    <form method="POST" action="{{ route('admin.approvals.approve', $user->id) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Setujui
                                                        </button>
                                                    </form>
                                                @endif

                                                
                                                @if($user->isPending() || $user->status === 'approved')
                                                    <form method="POST" action="{{ route('admin.approvals.reject', $user->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menolak akun {{ $user->name }}?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-semibold transition">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            Tolak
                                                        </button>
                                                    </form>
                                                @endif

                                                
                                                <form method="POST" action="{{ route('admin.approvals.destroy', $user->id) }}" onsubmit="return confirm('Hapus akun {{ $user->name }} secara permanen?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus User">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="font-medium text-gray-600">Tidak ada pengguna ditemukan</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba ganti filter atau kata kunci pencarian Anda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
