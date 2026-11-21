<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <x-favicon />

        <!-- PWA Manifest & Mobile Web App Meta -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#234066">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo/logofinansiabaru_192x192.png') }}">

        <!-- Prevent white flash on dark mode load -->
        <script>
            (function () {
                try {
                    const saved = localStorage.getItem('finansia_theme');
                    if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row">
            @include('layouts.navigation')

            <!-- Main Content Area (offset by sidebar on lg+) -->
            <div class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-200">
                @isset($header)
                    <header class="bg-white border-b border-gray-200/80 shadow-2xs">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-ai-chatbot />

        <!-- Quick PIN Lock Overlay (PWA) -->
        <div id="pin-lock-overlay" class="hidden fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-md flex items-center justify-center p-4">
            <div class="w-full max-w-xs bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-2xl text-center space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 mx-auto flex items-center justify-center text-xl">🔒</div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-slate-100">Finansia Terkunci</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Masukkan PIN cepat Anda untuk melanjutkan</p>
                </div>
                <input type="password" id="pin-unlock-input" inputmode="numeric" maxlength="6" placeholder="••••"
                       onkeydown="if(event.key==='Enter') verifyQuickPin()"
                       class="w-full text-center tracking-[0.5em] text-lg font-bold px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                <p id="pin-unlock-error" class="hidden text-xs font-bold text-rose-600">PIN salah, coba lagi.</p>
                <button type="button" onclick="verifyQuickPin()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl transition">
                    Buka Kunci
                </button>
            </div>
        </div>

        <!-- Pusat Notifikasi Modal -->
        <div id="notification-center-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="notif-modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" onclick="closeNotificationCenter()"></div>
            <div class="flex min-h-full items-start justify-center p-4 pt-16 sm:pt-20">
                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </span>
                            <div>
                                <h3 id="notif-modal-title" class="text-sm font-bold text-gray-900 dark:text-slate-100">Pusat Notifikasi</h3>
                                <p class="text-[11px] text-gray-500 dark:text-slate-400">Pengingat anggaran, rutin, hutang & future plan</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeNotificationCenter()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div id="notification-center-list" class="max-h-[65vh] overflow-y-auto p-4 space-y-2.5">
                        <p class="text-xs text-center text-gray-400 py-8">Memuat notifikasi...</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.updateDarkModeIcons = function () {
                const isDark = document.documentElement.classList.contains('dark');
                document.querySelectorAll('[data-theme-label]').forEach(el => {
                    el.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
                });
                document.querySelectorAll('[data-theme-icon-sun]').forEach(el => {
                    el.classList.toggle('hidden', !isDark);
                });
                document.querySelectorAll('[data-theme-icon-moon]').forEach(el => {
                    el.classList.toggle('hidden', isDark);
                });
            };

            window.toggleDarkMode = function () {
                const isDark = document.documentElement.classList.toggle('dark');
                try {
                    localStorage.setItem('finansia_theme', isDark ? 'dark' : 'light');
                } catch (e) {}
                window.updateDarkModeIcons();
            };

            // Privacy Mode (Sensor Nominal)
            window._privacyMutating = false;

            window.updatePrivacyIcons = function () {
                let enabled = false;
                try {
                    enabled = localStorage.getItem('finansia_privacy') === '1';
                } catch (e) {}

                document.querySelectorAll('[data-privacy-label]').forEach(el => {
                    el.textContent = enabled ? 'Tampilkan Nominal' : 'Sensor Nominal';
                });
                document.querySelectorAll('[data-privacy-icon-show]').forEach(el => {
                    el.classList.toggle('hidden', enabled);
                });
                document.querySelectorAll('[data-privacy-icon-hide]').forEach(el => {
                    el.classList.toggle('hidden', !enabled);
                });
            };

            window.applyPrivacyMode = function (enabled) {
                const mainEl = document.querySelector('main');
                if (!mainEl || window._privacyMutating) return;

                window._privacyMutating = true;
                try {
                    if (!enabled) {
                        mainEl.querySelectorAll('span.privacy-mask').forEach(span => {
                            const original = span.getAttribute('data-original') || '';
                            span.replaceWith(document.createTextNode(original));
                        });
                        return;
                    }

                    const skipTags = new Set(['SCRIPT', 'STYLE', 'INPUT', 'TEXTAREA', 'SELECT', 'OPTION', 'CANVAS', 'SVG']);
                    const walker = document.createTreeWalker(mainEl, NodeFilter.SHOW_TEXT, {
                        acceptNode(node) {
                            if (!node.nodeValue || !/Rp\s*[\d.,]+/.test(node.nodeValue)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            const parent = node.parentElement;
                            if (!parent || skipTags.has(parent.tagName) || parent.closest('.privacy-mask')) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            return NodeFilter.FILTER_ACCEPT;
                        }
                    });

                    const textNodes = [];
                    let current;
                    while ((current = walker.nextNode())) {
                        textNodes.push(current);
                    }

                    const regex = /Rp\s*[\d.,]+/g;
                    textNodes.forEach(node => {
                        const text = node.nodeValue;
                        let match;
                        let lastIndex = 0;
                        const frag = document.createDocumentFragment();

                        regex.lastIndex = 0;
                        while ((match = regex.exec(text)) !== null) {
                            if (match.index > lastIndex) {
                                frag.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
                            }
                            const span = document.createElement('span');
                            span.className = 'privacy-mask font-mono tracking-wider';
                            span.setAttribute('data-original', match[0]);
                            span.textContent = 'Rp ••••••';
                            frag.appendChild(span);
                            lastIndex = regex.lastIndex;
                        }

                        if (lastIndex < text.length) {
                            frag.appendChild(document.createTextNode(text.slice(lastIndex)));
                        }

                        node.parentNode.replaceChild(frag, node);
                    });
                } finally {
                    window._privacyMutating = false;
                }
            };

            window.togglePrivacyMode = function () {
                let enabled = false;
                try {
                    enabled = localStorage.getItem('finansia_privacy') === '1';
                    localStorage.setItem('finansia_privacy', enabled ? '0' : '1');
                    enabled = !enabled;
                } catch (e) {}

                window.applyPrivacyMode(enabled);
                window.updatePrivacyIcons();
            };

            // Notification Center
            window.renderNotificationFeed = function (items) {
                const listEl = document.getElementById('notification-center-list');
                if (!listEl) return;

                if (!items || items.length === 0) {
                    listEl.innerHTML = '<div class="text-center py-8"><p class="text-sm font-semibold text-gray-600 dark:text-slate-300">Semua aman!</p><p class="text-xs text-gray-400 mt-1">Tidak ada tagihan mendesak atau anggaran yang melebihi batas.</p></div>';
                    return;
                }

                const severityStyles = {
                    danger: 'bg-rose-50 border-rose-200 text-rose-900 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-200',
                    warning: 'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-950/40 dark:border-amber-900 dark:text-amber-200',
                    info: 'bg-indigo-50 border-indigo-200 text-indigo-900 dark:bg-indigo-950/40 dark:border-indigo-900 dark:text-indigo-200',
                };

                listEl.innerHTML = items.map(item => {
                    const cls = severityStyles[item.severity] || severityStyles.info;
                    const escapedTitle = String(item.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const escapedMsg = String(item.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const escapedCat = String(item.category || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const escapedIcon = String(item.icon || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const href = item.url || '#';
                    return `<a href="${href}" class="block p-3.5 rounded-xl border ${cls} hover:opacity-90 transition">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-bold">${escapedIcon ? escapedIcon + ' ' : ''}${escapedTitle}</p>
                            <span class="text-[10px] font-semibold uppercase tracking-wide opacity-75 shrink-0">${escapedCat}</span>
                        </div>
                        <p class="text-xs mt-1 opacity-90">${escapedMsg}</p>
                    </a>`;
                }).join('');
            };

            window.loadNotificationFeed = function (renderList) {
                fetch('{{ route('notifications.feed') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (!data) return;
                    const count = Number(data.count || 0);
                    ['notif-badge-desktop', 'notif-badge-mobile'].forEach(id => {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.classList.toggle('hidden', count === 0);
                        if (id === 'notif-badge-mobile') {
                            badge.classList.toggle('flex', count > 0);
                        }
                    });
                    if (renderList) {
                        window.renderNotificationFeed(data.items || []);
                    }
                })
                .catch(() => {});
            };

            window.openNotificationCenter = function () {
                const modal = document.getElementById('notification-center-modal');
                if (!modal) return;
                modal.classList.remove('hidden');
                window.loadNotificationFeed(true);
            };

            window.closeNotificationCenter = function () {
                const modal = document.getElementById('notification-center-modal');
                if (modal) modal.classList.add('hidden');
            };

            // Quick PIN Lock (PWA)
            window._hashPin = function (pin) {
                let h = 2166136261;
                for (let i = 0; i < pin.length; i++) {
                    h ^= pin.charCodeAt(i);
                    h = Math.imul(h, 16777619);
                }
                return 'fin_' + (h >>> 0).toString(16);
            };

            window.refreshPinUi = function () {
                let hasPin = false;
                try { hasPin = !!localStorage.getItem('finansia_pin_hash'); } catch (e) {}
                const badge = document.getElementById('pin-status-badge');
                const btnLock = document.getElementById('btn-lock-now');
                const btnRemove = document.getElementById('btn-remove-pin');
                if (badge) {
                    badge.textContent = hasPin ? 'Aktif' : 'Nonaktif';
                    badge.className = hasPin
                        ? 'px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700'
                        : 'px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600';
                }
                if (btnLock) btnLock.classList.toggle('hidden', !hasPin);
                if (btnRemove) btnRemove.classList.toggle('hidden', !hasPin);
            };

            window.saveQuickPin = function () {
                const inp = document.getElementById('profile-pin-input');
                const fb = document.getElementById('pin-feedback');
                const val = (inp?.value || '').trim();
                if (!/^\d{4,6}$/.test(val)) {
                    if (fb) { fb.textContent = 'PIN harus 4 sampai 6 digit angka.'; fb.className = 'text-xs font-semibold text-rose-600'; fb.classList.remove('hidden'); }
                    return;
                }
                try {
                    localStorage.setItem('finansia_pin_hash', window._hashPin(val));
                    sessionStorage.setItem('finansia_pin_unlocked', '1');
                } catch (e) {}
                if (inp) inp.value = '';
                if (fb) { fb.textContent = 'PIN cepat berhasil disimpan di perangkat ini.'; fb.className = 'text-xs font-semibold text-emerald-700'; fb.classList.remove('hidden'); }
                window.refreshPinUi();
            };

            window.removeQuickPin = function () {
                try {
                    localStorage.removeItem('finansia_pin_hash');
                    sessionStorage.removeItem('finansia_pin_unlocked');
                } catch (e) {}
                const fb = document.getElementById('pin-feedback');
                if (fb) { fb.textContent = 'PIN cepat telah dihapus.'; fb.className = 'text-xs font-semibold text-gray-600'; fb.classList.remove('hidden'); }
                window.refreshPinUi();
            };

            window.lockAppWithPin = function () {
                try { sessionStorage.removeItem('finansia_pin_unlocked'); } catch (e) {}
                const overlay = document.getElementById('pin-lock-overlay');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    const inp = document.getElementById('pin-unlock-input');
                    if (inp) { inp.value = ''; inp.focus(); }
                }
            };

            window.verifyQuickPin = function () {
                const inp = document.getElementById('pin-unlock-input');
                const err = document.getElementById('pin-unlock-error');
                const val = (inp?.value || '').trim();
                let saved = '';
                try { saved = localStorage.getItem('finansia_pin_hash') || ''; } catch (e) {}
                if (saved && window._hashPin(val) === saved) {
                    try { sessionStorage.setItem('finansia_pin_unlocked', '1'); } catch (e) {}
                    document.getElementById('pin-lock-overlay')?.classList.add('hidden');
                    if (err) err.classList.add('hidden');
                } else if (err) {
                    err.classList.remove('hidden');
                }
            };

            document.addEventListener('DOMContentLoaded', function () {
                window.updateDarkModeIcons();
                window.updatePrivacyIcons();
                window.refreshPinUi();
                window.loadNotificationFeed(false);

                try {
                    if (localStorage.getItem('finansia_pin_hash') && sessionStorage.getItem('finansia_pin_unlocked') !== '1') {
                        window.lockAppWithPin();
                    }
                } catch (e) {}

                let privacyEnabled = false;
                try {
                    privacyEnabled = localStorage.getItem('finansia_privacy') === '1';
                } catch (e) {}

                if (privacyEnabled) {
                    window.applyPrivacyMode(true);
                }

                const mainEl = document.querySelector('main');
                if (mainEl && typeof MutationObserver !== 'undefined') {
                    const observer = new MutationObserver(function () {
                        try {
                            if (localStorage.getItem('finansia_privacy') === '1' && !window._privacyMutating) {
                                window.applyPrivacyMode(true);
                            }
                        } catch (e) {}
                    });
                    observer.observe(mainEl, { childList: true, subtree: true });
                }
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js').catch(function () {});
                });
            }
        </script>
    </body>
</html>
