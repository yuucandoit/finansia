<style>
    #ai-chat-messages::-webkit-scrollbar,
    #ai-chatbot-window .overflow-y-auto::-webkit-scrollbar {
        width: 5px;
    }
    #ai-chat-messages::-webkit-scrollbar-track,
    #ai-chatbot-window .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    #ai-chat-messages::-webkit-scrollbar-thumb,
    #ai-chatbot-window .overflow-y-auto::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 9999px;
    }
    #ai-chat-messages::-webkit-scrollbar-thumb:hover,
    #ai-chatbot-window .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
</style>

<div id="ai-chatbot-container" class="fixed bottom-4 sm:bottom-6 right-3 sm:right-6 z-50 flex flex-col items-end font-sans max-w-[calc(100vw-24px)]">

    <!-- Chat Window Card -->
    <div id="ai-chatbot-window" 
         class="hidden w-[calc(100vw-24px)] sm:w-[440px] max-w-[92vw] max-h-[85vh] sm:max-h-[680px] bg-white rounded-3xl shadow-2xl border border-gray-200/90 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right">
        
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-[#162844] via-[#234066] to-indigo-700 px-4 sm:px-5 py-3.5 flex items-center justify-between text-white select-none shadow-md">
            <div class="flex items-center gap-3">
                <!-- Bot Avatar Icon -->
                <div class="relative w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center shadow-inner flex-shrink-0 border border-white/25">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <path d="M12 5.5v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                        <circle cx="9" cy="12" r="1.8" fill="white" />
                        <circle cx="15" cy="12" r="1.8" fill="white" />
                        <circle cx="9" cy="12" r="0.8" fill="#234066" />
                        <circle cx="15" cy="12" r="0.8" fill="#234066" />
                        <path d="M10 15h4" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-[#234066] rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-bold text-sm sm:text-base tracking-tight text-white leading-tight">Finansia Advisor</h3>
                        <span class="px-1.5 py-0.5 rounded-md bg-white/15 text-[9px] font-bold uppercase tracking-wider text-blue-100">AI</span>
                    </div>
                    <p class="text-[11px] text-blue-100/90 flex items-center gap-1.5 mt-0.5">
                        Online • Catat & Konsultasi Keuangan
                    </p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-1">
                <button type="button" 
                        onclick="toggleVoiceMode()" 
                        id="ai-voice-mode-btn"
                        class="inline-flex items-center gap-1 text-white/80 hover:text-white px-2.5 py-1.5 rounded-xl hover:bg-white/15 transition-colors text-xs font-semibold"
                        title="Mode Ngobrol Suara (Voice & Transcriptor)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                    <span class="hidden sm:inline">Ngobrol</span>
                </button>
                <button type="button" 
                        onclick="toggleAiExpand()" 
                        id="ai-expand-btn"
                        class="inline-flex text-white/75 hover:text-white p-2 rounded-xl hover:bg-white/15 transition-colors"
                        title="Full Screen / Perkecil Jendela">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </button>
                <button type="button" 
                        onclick="resetAiChat()" 
                        class="text-white/75 hover:text-white p-2 rounded-xl hover:bg-white/15 transition-colors"
                        title="Mulai percakapan baru / Reset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
                <button type="button" 
                        onclick="toggleAiChatbot()" 
                        class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/15 transition-colors"
                        title="Tutup / Minimize">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Quick Mode Bar -->
        <div class="px-3.5 py-2 bg-white border-b border-gray-100 flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
            <button type="button" onclick="quickFillInput('Cek kesehatan keuangan saya bulan ini dan kasih saran hematnya')"
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/70 whitespace-nowrap transition">
                🩺 Cek Kesehatan
            </button>
            <button type="button" onclick="quickFillInput('Saya boros di mana bulan ini dan gimana strategi hematnya?')"
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/70 whitespace-nowrap transition">
                💡 Analisis Boros
            </button>
            <button type="button" onclick="quickFillInput('Beli kopi 25rb pake BCA')"
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/70 whitespace-nowrap transition">
                💸 Catat Cepat
            </button>
            <button type="button" onclick="quickFillInput('Atur anggaran makan 1.5jt bulan ini')"
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/70 whitespace-nowrap transition">
                🎯 Atur Budget
            </button>
            <button type="button" onclick="quickFillInput('Buatkan rencana rakit PC budget 15jt selama 3 bulan')"
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200/70 whitespace-nowrap transition">
                🗓️ Buat Rencana AI
            </button>
        </div>

        <!-- Chat Messages Container -->
        <div id="ai-chat-messages" class="p-3.5 sm:p-4 space-y-3 sm:space-y-3.5 bg-slate-50/70 flex-1 max-h-[54vh] sm:max-h-[470px] min-h-[300px] overflow-y-auto scroll-smooth">
            
            <!-- Greeting Message -->
            <div class="flex justify-start gap-2.5 items-start">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#1b3252] to-[#2e5283] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                    </svg>
                </div>
                <div class="bg-white text-gray-800 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[88%] leading-relaxed border border-gray-200/70 shadow-xs space-y-1">
                    <p class="font-bold text-gray-900">Halo! Ada yang ingin dicatat hari ini? 😊</p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Anda bisa catat transaksi harian atau atur anggaran bulanan secara santai (bisa beberapa sekaligus).
                    </p>
                </div>
            </div>

            <!-- Quick Suggestions (Hidden once conversation starts) -->
            <div id="ai-quick-suggestions" class="space-y-1.5 pt-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">💡 Contoh Cepat</p>
                <div class="grid grid-cols-1 gap-1.5 text-xs text-slate-700">
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Atur anggaran bayar kos 1.5jt dan sedekah 200rb">
                        <span class="truncate">🎯 Atur anggaran bayar kos 1.5jt & sedekah 200rb</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Beli kopi 25k dan bensin 30k pake BCA">
                        <span class="truncate">☕ Beli kopi 25k & bensin 30k pake BCA</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Bulan ini jajan makan udah habis berapa?">
                        <span class="truncate">📊 Bulan ini jajan makan udah habis berapa?</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-indigo-50/60 hover:bg-indigo-100/70 border border-indigo-200/80 hover:border-indigo-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Cek kesehatan keuangan saya bulan ini dan kasih saran hematnya">
                        <span class="truncate font-medium text-indigo-900">🩺 Cek kesehatan keuangan & saran bulan ini</span>
                        <span class="text-indigo-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Tanya →</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Voice Conversation & Live Transcriptor Panel (Mode Ngobrol) -->
        <div id="ai-voice-panel" class="hidden bg-gradient-to-b from-indigo-950 to-[#162844] text-white px-4 py-4 border-b border-indigo-800/60 shrink-0">
            <div class="max-w-2xl mx-auto flex flex-col items-center text-center space-y-3">
                <div class="w-full flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1.5 font-bold text-indigo-200">
                        <span id="ai-voice-status-dot" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span id="ai-voice-status-text">Mode Ngobrol Aktif • Siap Mendengarkan</span>
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAiTts()" id="ai-tts-toggle-btn"
                                class="px-2 py-0.5 rounded-lg bg-white/10 hover:bg-white/20 text-[10px] font-semibold transition">
                            🔊 Suara AI: ON
                        </button>
                        <button type="button" onclick="toggleVoiceMode()" class="text-white/70 hover:text-white text-xs font-bold px-1.5">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Live Transcriptor Display -->
                <div id="ai-voice-transcript-box" class="w-full min-h-[44px] max-h-24 overflow-y-auto bg-white/10 border border-white/15 rounded-2xl px-3.5 py-2 text-xs text-blue-50 italic flex items-center justify-center">
                    <span id="ai-voice-transcript">Tekan & tahan tombol mikrofon di bawah untuk mulai berbicara...</span>
                </div>

                <!-- Push-to-Talk Big Button -->
                <div class="flex items-center gap-3">
                    <button type="button"
                            id="ai-ptt-hold-btn"
                            onmousedown="startPushToTalk(true)"
                            onmouseup="stopPushToTalk(true)"
                            onmouseleave="stopPushToTalk(true)"
                            ontouchstart="event.preventDefault(); startPushToTalk(true);"
                            ontouchend="event.preventDefault(); stopPushToTalk(true);"
                            class="select-none px-5 py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-xs shadow-lg shadow-emerald-900/40 flex items-center gap-2 transition-all active:scale-95">
                        <span>🎙️</span>
                        <span id="ai-ptt-label">Tekan & Tahan untuk Bicara</span>
                    </button>

                    <button type="button"
                            onclick="stopAiSpeech()"
                            class="px-3 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition"
                            title="Hentikan suara AI">
                        ⏹️ Stop Suara
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Input Form -->
        <div class="p-3 sm:p-3.5 bg-white border-t border-gray-100">
            <form id="ai-chatbot-form" onsubmit="handleAiChatSubmit(event)" class="relative flex items-end gap-1.5 bg-gray-50 border border-gray-200 rounded-2xl p-1.5 focus-within:border-[#234066] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#234066]/10 transition-all">
                <input type="file"
                       id="ai-image-input"
                       accept="image/png,image/jpeg,image/webp"
                       class="hidden"
                       onchange="handleAiImageSelect(this)">
                <button type="button"
                        onclick="document.getElementById('ai-image-input').click()"
                        class="w-9 h-9 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all flex items-center justify-center flex-shrink-0"
                        title="Upload Screenshot M-Banking / Foto Struk / Slip Gaji (OCR)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                </button>

                <!-- Push-to-Text Mic Button -->
                <button type="button"
                        id="ai-mic-btn"
                        onclick="togglePushToText()"
                        class="w-9 h-9 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all flex items-center justify-center flex-shrink-0"
                        title="Push to Text (Dikte Suara ke Teks)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                </button>

                <textarea 
                       id="ai-chatbot-input" 
                       rows="1"
                       placeholder="Ketik pesan atau upload struk/m-banking..." 
                       class="flex-1 bg-transparent border-0 px-1.5 py-1.5 text-sm text-gray-800 outline-none placeholder:text-gray-400 resize-none max-h-32 min-h-[38px] leading-relaxed scroll-smooth"></textarea>
                
                <button type="submit" 
                        id="ai-chatbot-send-btn"
                        class="w-9 h-9 text-white bg-[#234066] hover:bg-[#1a304e] rounded-xl transition-all flex items-center justify-center flex-shrink-0 disabled:opacity-40 shadow-xs active:scale-95"
                        title="Kirim pesan (Ctrl+Enter)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
            <div class="flex items-center justify-between px-2 mt-1.5 text-[10px] text-gray-400 select-none">
                <span><kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Enter</kbd> kirim pesan</span>
                <span><kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Shift</kbd>+<kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Enter</kbd> baris baru</span>
            </div>
        </div>

    </div>

    <!-- Floating Trigger Button -->
    <button id="ai-chatbot-trigger" 
            type="button"
            onclick="toggleAiChatbot()" 
            class="bg-gradient-to-r from-[#234066] to-indigo-600 hover:from-[#1a304e] hover:to-indigo-700 text-white p-3 sm:p-3.5 rounded-full shadow-2xl hover:shadow-indigo-900/30 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2.5 group border border-white/15">
        <div class="relative w-8 h-8 rounded-full bg-white flex items-center justify-center flex-shrink-0 shadow-xs">
            <svg class="w-5 h-5 text-[#234066]" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="4" r="1.5" />
                <path d="M12 5.5v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <rect x="5" y="7" width="14" height="11" rx="4" />
                <circle cx="9" cy="12" r="1.8" fill="white" />
                <circle cx="15" cy="12" r="1.8" fill="white" />
                <circle cx="9" cy="12" r="0.8" fill="#234066" />
                <circle cx="15" cy="12" r="0.8" fill="#234066" />
                <path d="M10 15h4" stroke="white" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 border-2 border-white rounded-full animate-pulse"></span>
        </div>
        <span class="text-sm font-bold pr-1.5 tracking-tight">Finansia AI</span>
    </button>

</div>

<script>
    window.aiPendingTransactions = window.aiPendingTransactions || {};
    window.aiPendingBudgets = window.aiPendingBudgets || {};

    document.addEventListener('DOMContentLoaded', function() {
        initChatInputEvents();
    });

    function initChatInputEvents() {
        const input = document.getElementById('ai-chatbot-input');
        if (!input) return;

        // Auto expand textarea height on typing
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 128) + 'px';
        });

        // Enter untuk kirim pesan, Shift+Enter untuk baris baru
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleAiChatSubmit(e);
            }
        });
    }

    function toggleAiExpand() {
        const container = document.getElementById('ai-chatbot-container');
        const win = document.getElementById('ai-chatbot-window');
        const msgBox = document.getElementById('ai-chat-messages');
        if (!win || !container) return;

        const isExpanded = win.dataset.expanded === 'true';
        if (isExpanded) {
            container.className = 'fixed bottom-4 sm:bottom-6 right-3 sm:right-6 z-50 flex flex-col items-end font-sans max-w-[calc(100vw-24px)]';
            win.className = 'w-[calc(100vw-24px)] sm:w-[440px] max-w-[92vw] max-h-[85vh] sm:max-h-[680px] bg-white rounded-3xl shadow-2xl border border-gray-200/90 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right';
            if (msgBox) {
                msgBox.className = 'p-3.5 sm:p-4 space-y-3 sm:space-y-3.5 bg-slate-50/70 flex-1 max-h-[54vh] sm:max-h-[470px] min-h-[300px] overflow-y-auto scroll-smooth';
            }
            win.dataset.expanded = 'false';
        } else {
            container.className = 'fixed inset-y-0 right-0 z-[60] flex flex-col font-sans w-full sm:w-[460px] lg:w-[520px] h-screen max-w-none';
            win.className = 'w-full h-full max-w-none max-h-none bg-white rounded-none shadow-2xl border-l border-gray-200 overflow-hidden flex flex-col transition-all duration-200';
            if (msgBox) {
                msgBox.className = 'p-4 sm:p-5 space-y-3.5 bg-slate-50/70 flex-1 overflow-y-auto scroll-smooth w-full';
            }
            win.dataset.expanded = 'true';
        }
    }

    function toggleAiChatbot() {
        const chatWindow = document.getElementById('ai-chatbot-window');
        const triggerBtn = document.getElementById('ai-chatbot-trigger');

        if (!chatWindow || !triggerBtn) return;

        if (chatWindow.classList.contains('hidden')) {
            chatWindow.classList.remove('hidden');
            triggerBtn.classList.add('hidden');
            const input = document.getElementById('ai-chatbot-input');
            if (input) {
                input.focus();
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 128) + 'px';
            }
        } else {
            chatWindow.classList.add('hidden');
            triggerBtn.classList.remove('hidden');
        }
    }

    function quickFillInput(text) {
        const input = document.getElementById('ai-chatbot-input');
        if (input) {
            input.value = text.replace(/^["'\s]+|["'\s]+$/g, '');
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 128) + 'px';
            input.focus();
        }
    }

    function resetAiChat() {
        if (!confirm('Bersihkan percakapan ini dan mulai dari awal?')) return;
        const container = document.getElementById('ai-chat-messages');
        if (!container) return;

        container.innerHTML = `
            <!-- Greeting Message -->
            <div class="flex justify-start gap-2.5 items-start">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#1b3252] to-[#2e5283] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                    </svg>
                </div>
                <div class="bg-white text-gray-800 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[88%] leading-relaxed border border-gray-200/70 shadow-xs space-y-1">
                    <p class="font-bold text-gray-900">Halo! Ada yang ingin dicatat hari ini? 😊</p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Anda bisa catat transaksi harian atau atur anggaran bulanan secara santai (bisa beberapa sekaligus).
                    </p>
                </div>
            </div>

            <!-- Quick Suggestions -->
            <div id="ai-quick-suggestions" class="space-y-1.5 pt-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">💡 Contoh Cepat</p>
                <div class="grid grid-cols-1 gap-1.5 text-xs text-slate-700">
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Atur anggaran bayar kos 1.5jt dan sedekah 200rb">
                        <span class="truncate">🎯 Atur anggaran bayar kos 1.5jt & sedekah 200rb</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Beli kopi 25k dan bensin 30k pake BCA">
                        <span class="truncate">☕ Beli kopi 25k & bensin 30k pake BCA</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-white hover:bg-blue-50/80 border border-gray-200/80 hover:border-blue-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Bulan ini jajan makan udah habis berapa?">
                        <span class="truncate">📊 Bulan ini jajan makan udah habis berapa?</span>
                        <span class="text-blue-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pakai →</span>
                    </button>
                    <button type="button" 
                            class="text-left bg-indigo-50/60 hover:bg-indigo-100/70 border border-indigo-200/80 hover:border-indigo-300 px-3 py-2 rounded-xl transition shadow-2xs group flex items-center justify-between" 
                            onclick="quickFillInput(this.dataset.prompt)" 
                            data-prompt="Cek kesehatan keuangan saya bulan ini dan kasih saran hematnya">
                        <span class="truncate font-medium text-indigo-900">🩺 Cek kesehatan keuangan & saran bulan ini</span>
                        <span class="text-indigo-600 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">Tanya →</span>
                    </button>
                </div>
            </div>
        `;

        window.aiPendingTransactions = {};
        window.aiPendingBudgets = {};
        window.aiChatHistory = [];
        const input = document.getElementById('ai-chatbot-input');
        if (input) {
            input.value = '';
            input.focus();
        }
    }

    async function handleAiChatSubmit(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('ai-chatbot-input');
        const sendBtn = document.getElementById('ai-chatbot-send-btn');
        const messageContainer = document.getElementById('ai-chat-messages');

        if (!input || !input.value.trim() || !messageContainer) return;

        const userText = input.value.trim();

        // Sembunyikan saran cepat ketika percakapan sudah dimulai agar ruang chat lega
        document.getElementById('ai-quick-suggestions')?.classList.add('hidden');

        const userBubble = document.createElement('div');
        userBubble.className = 'flex justify-end animate-fade-in';
        const formattedUserText = escapeHtml(userText).replace(/\n/g, '<br>');
        userBubble.innerHTML = `
            <div class="bg-[#234066] text-white text-sm font-medium px-4 py-2.5 rounded-2xl rounded-tr-xs max-w-[85%] shadow-xs leading-relaxed whitespace-pre-line break-words">
                ${formattedUserText}
            </div>
        `;
        messageContainer.appendChild(userBubble);
        input.value = '';
        input.style.height = 'auto';
        input.disabled = true;
        sendBtn.disabled = true;
        messageContainer.scrollTop = messageContainer.scrollHeight;

        const loadingId = 'ai-loading-' + Date.now();
        const loadingEl = document.createElement('div');
        loadingEl.id = loadingId;
        loadingEl.className = 'flex items-center gap-2 text-xs text-gray-400 p-2 italic';
        loadingEl.innerHTML = `
            <span class="inline-block w-2 h-2 rounded-full bg-[#234066] animate-bounce"></span>
            <span class="inline-block w-2 h-2 rounded-full bg-[#234066] animate-bounce [animation-delay:0.2s]"></span>
            <span class="inline-block w-2 h-2 rounded-full bg-[#234066] animate-bounce [animation-delay:0.4s]"></span>
            <span>Asisten AI sedang memproses...</span>
        `;
        messageContainer.appendChild(loadingEl);
        messageContainer.scrollTop = messageContainer.scrollHeight;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            window.aiChatHistory = window.aiChatHistory || [];
            const historyPayload = window.aiChatHistory.slice(-10);

            const response = await fetch('/ai-chat/parse', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    message: userText,
                    history: historyPayload
                })
            });

            document.getElementById(loadingId)?.remove();
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();

            if (response.ok) {
                const resData = await response.json();
                window.aiChatHistory.push({ role: 'user', content: userText });
                if (resData.reply) {
                    window.aiChatHistory.push({ role: 'assistant', content: resData.reply });
                }

                if (resData.intent === 'RECORD_FUTURE_PLAN' && resData.data) {
                    renderFuturePlanConfirmationCard(resData.data, resData.reply, messageContainer);
                } else if (resData.intent === 'RECORD_BUDGET' && resData.data) {
                    renderBudgetConfirmationCard(resData.data, resData.reply, messageContainer, resData.is_multiple);
                } else if (resData.intent === 'RECORD_TRANSACTION' && resData.data) {
                    renderConfirmationCard(resData.data, resData.reply, messageContainer, resData.is_multiple);
                } else {
                    renderBotTextReply(resData.reply || 'Pesan Anda telah diterima.', messageContainer);
                }

                if (window.aiVoiceMode && window.aiTtsEnabled && resData.reply) {
                    speakAiText(resData.reply);
                }
            } else {
                const errData = await response.json().catch(() => ({}));
                renderBotTextReply(errData.reply || errData.message || 'Maaf, terjadi kendala saat memproses permintaan. Coba ulangi kembali ya.', messageContainer);
            }
        } catch (err) {
            document.getElementById(loadingId)?.remove();
            input.disabled = false;
            sendBtn.disabled = false;
            renderBotTextReply('Koneksi terputus. Silakan periksa jaringan Anda.', messageContainer);
        }

        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // ==========================================
    // 1. CONFIRMATION FOR TRANSACTIONS
    // ==========================================
    function renderConfirmationCard(data, replyText, container, isMultiple) {
        if (replyText) {
            renderBotTextReply(replyText, container);
        }

        const items = data.items || (data.amount ? [data] : []);

        // Jika multiple transactions
        if (isMultiple || items.length > 1) {
            renderMultipleTransactionsCard(data, container);
            return;
        }

        // Single transaction card
        const singleData = items[0] || data;
        const cardId = 'card-tx-' + Date.now();
        window.aiPendingTransactions[cardId] = singleData;

        const isExpense = singleData.type !== 'Cash In';
        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3';

        let savingsOptions = '';
        if (data.available_savings && data.available_savings.length > 0) {
            savingsOptions = data.available_savings.map(s => {
                const balFormatted = s.balance_formatted || ('Rp ' + Number(s.balance || 0).toLocaleString('id-ID'));
                return `<option value="${s.id}" data-balance="${s.balance || 0}" ${s.id === singleData.savings_id ? 'selected' : ''}>${escapeHtml(s.name)} (Saldo: ${balFormatted})</option>`;
            }).join('');
        } else {
            const initialBalFormatted = singleData.saving_balance_formatted || ('Rp ' + Number(singleData.saving_balance || 0).toLocaleString('id-ID'));
            savingsOptions = `<option value="${singleData.savings_id}" data-balance="${singleData.saving_balance || 0}" selected>${escapeHtml(singleData.account_name)} (Saldo: ${initialBalFormatted})</option>`;
        }

        let categoryOptions = '';
        if (data.available_categories && data.available_categories.length > 0) {
            categoryOptions = data.available_categories.map(c => 
                `<option value="${c.id}" ${c.id === singleData.category_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
            ).join('');
        } else {
            categoryOptions = `<option value="${singleData.category_id}" selected>${escapeHtml(singleData.category_name)}</option>`;
        }

        const selectedSaving = (data.available_savings || []).find(s => s.id === singleData.savings_id);
        const currentBal = selectedSaving ? Number(selectedSaving.balance) : Number(singleData.saving_balance || 0);
        const isInsufficient = isExpense && currentBal < Number(singleData.amount);

        cardEl.innerHTML = `
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Draft Transaksi</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md ${isExpense ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'}">${escapeHtml(singleData.type_label || singleData.type)}</span>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Deskripsi:</span>
                    <input type="text" id="input-desc-${cardId}" value="${escapeHtml(singleData.description || '')}" 
                           placeholder="Deskripsi..."
                           class="flex-1 text-sm text-gray-800 font-semibold bg-gray-50 border border-gray-200 rounded-lg py-1 px-2.5 outline-none focus:ring-1 focus:ring-[#234066] focus:border-[#234066]" />
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Nominal:</span>
                    <span class="text-gray-900 font-bold text-base text-[#234066]">${escapeHtml(singleData.amount_formatted)}</span>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Rekening:</span>
                    <select id="select-saving-${cardId}" 
                            class="flex-1 text-xs text-gray-800 font-semibold bg-gray-50 border border-gray-200 rounded-lg py-1.5 px-2 outline-none focus:ring-1 focus:ring-[#234066]">
                        ${savingsOptions}
                    </select>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Kategori:</span>
                    <select id="select-category-${cardId}" 
                            class="flex-1 text-xs text-gray-800 font-medium bg-gray-50 border border-gray-200 rounded-lg py-1.5 px-2 outline-none focus:ring-1 focus:ring-[#234066]">
                        ${categoryOptions}
                    </select>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Tanggal:</span>
                    <span class="text-gray-800 text-xs">${escapeHtml(singleData.date_formatted || singleData.date)}</span>
                </div>
            </div>

            <div id="balance-warning-${cardId}" class="${isInsufficient ? 'flex' : 'hidden'} items-start gap-2 p-2 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium">
                <span id="balance-warning-text-${cardId}">Saldo rekening ini (Rp ${currentBal.toLocaleString('id-ID')}) tidak mencukupi untuk transaksi sebesar ${escapeHtml(singleData.amount_formatted)}.</span>
            </div>

            <hr class="border-gray-200 my-2">

            <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                <button type="button" 
                        onclick="cancelTransaction('${cardId}')"
                        class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 text-xs font-semibold rounded-xl transition-all">
                    Batal
                </button>
                <button type="button" 
                        id="btn-confirm-${cardId}"
                        onclick="confirmAndSaveTransaction('${cardId}')"
                        ${isInsufficient ? 'disabled' : ''}
                        class="flex-1 px-4 py-2.5 bg-[#234066] hover:bg-[#1a304e] text-white text-xs font-bold rounded-xl shadow-sm transition-all text-center flex items-center justify-center gap-1.5 ${isInsufficient ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.01] active:scale-[0.99]'}">
                    <span>✓ Konfirmasi & Simpan</span>
                </button>
            </div>
        `;
        container.appendChild(cardEl);

        setTimeout(() => {
            const selSaving = document.getElementById('select-saving-' + cardId);
            if (selSaving) {
                selSaving.addEventListener('change', function() {
                    const selectedOpt = this.options[this.selectedIndex];
                    const optBal = parseFloat(selectedOpt.getAttribute('data-balance') || '0');
                    const warnEl = document.getElementById('balance-warning-' + cardId);
                    const warnTextEl = document.getElementById('balance-warning-text-' + cardId);
                    const btnConf = document.getElementById('btn-confirm-' + cardId);

                    if (isExpense && optBal < parseFloat(singleData.amount)) {
                        if (warnEl) warnEl.classList.remove('hidden');
                        if (warnEl) warnEl.classList.add('flex');
                        if (warnTextEl) warnTextEl.textContent = `Saldo rekening ini (Rp ${optBal.toLocaleString('id-ID')}) tidak mencukupi untuk transaksi sebesar ${singleData.amount_formatted}.`;
                        if (btnConf) {
                            btnConf.disabled = true;
                            btnConf.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    } else {
                        if (warnEl) {
                            warnEl.classList.add('hidden');
                            warnEl.classList.remove('flex');
                        }
                        if (btnConf) {
                            btnConf.disabled = false;
                            btnConf.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
            }
        }, 50);
    }

    // Render Multiple Transactions Card
    function renderMultipleTransactionsCard(data, container) {
        const cardId = 'card-multi-tx-' + Date.now();
        window.aiPendingTransactions[cardId] = data;

        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3';

        let itemsHtml = '';
        data.items.forEach((item, idx) => {
            const isExpense = item.type !== 'Cash In';
            itemsHtml += `
                <div class="p-2.5 bg-gray-50 border border-gray-100 rounded-xl space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-800">#${idx + 1} ${escapeHtml(item.description)}</span>
                        <span class="font-extrabold ${isExpense ? 'text-red-600' : 'text-emerald-600'}">${escapeHtml(item.amount_formatted)}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span>💳 ${escapeHtml(item.account_name)}</span>
                        <span>🏷️ ${escapeHtml(item.category_name)}</span>
                    </div>
                </div>
            `;
        });

        cardEl.innerHTML = `
            <div class="space-y-2.5">
                <div class="flex items-center justify-between pb-1.5 border-b border-gray-100">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-gray-800">📝 Draft ${data.items.length} Transaksi</span>
                    </div>
                    <span class="text-xs font-extrabold text-[#234066]">${escapeHtml(data.total_amount_formatted)}</span>
                </div>

                <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                    ${itemsHtml}
                </div>

                <hr class="border-gray-200 my-2">

                <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                    <button type="button" 
                            onclick="cancelTransaction('${cardId}')"
                            class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 text-xs font-semibold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="button" 
                            onclick="confirmAndSaveMultipleTransactions('${cardId}')"
                            class="flex-1 px-4 py-2.5 bg-[#234066] hover:bg-[#1a304e] text-white text-xs font-bold rounded-xl shadow-sm transition-all text-center flex items-center justify-center gap-1.5 hover:scale-[1.01] active:scale-[0.99]">
                        <span>✓ Simpan Semua (${data.items.length} Transaksi)</span>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(cardEl);
    }

    async function confirmAndSaveMultipleTransactions(cardId) {
        const data = window.aiPendingTransactions[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);

        if (!data || !data.items) {
            alert('Data transaksi tidak ditemukan.');
            return;
        }

        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="w-full text-center py-2 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-[#234066] flex items-center justify-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 rounded-full border-2 border-[#234066] border-t-transparent animate-spin"></span>
                    <span>Menyimpan ${data.items.length} transaksi...</span>
                </div>
            `;
        }

        const payload = {
            items: data.items.map(it => ({
                savings_id: it.savings_id,
                category_id: it.category_id,
                type: it.type,
                amount: it.amount,
                description: it.description,
                transaction_date: it.date
            }))
        };

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/ai-chat/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            const resData = await response.json();
            const messageContainer = document.getElementById('ai-chat-messages');

            if (response.ok) {
                delete window.aiPendingTransactions[cardId];
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-700 flex items-center justify-center gap-1.5">
                            <span>✓ Berhasil! ${data.items.length} transaksi telah disimpan</span>
                        </div>
                    `;
                }
                renderBotSuccessReply(`<strong>Berhasil!</strong> Sebanyak <strong>${data.items.length} transaksi</strong> telah berhasil dicatat ke saldo rekening Anda.`, messageContainer);
            } else {
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                            ✕ Gagal: ${escapeHtml(resData.message || 'Terjadi kesalahan')}
                        </div>
                    `;
                }
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }

        const messageContainer = document.getElementById('ai-chat-messages');
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // ==========================================
    // 2. CONFIRMATION FOR BUDGETS
    // ==========================================
    function renderBudgetConfirmationCard(data, replyText, container, isMultiple) {
        if (replyText) {
            renderBotTextReply(replyText, container);
        }

        const items = data.items || [];
        const cardId = 'card-budget-' + Date.now();
        window.aiPendingBudgets[cardId] = data;

        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3';

        let itemsHtml = '';
        items.forEach((item, idx) => {
            itemsHtml += `
                <div class="p-2.5 bg-blue-50/60 border border-blue-100 rounded-xl space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900">🎯 ${escapeHtml(item.name || item.category_name)}</span>
                        <span class="font-extrabold text-[#234066]">${escapeHtml(item.amount_formatted)}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span class="inline-flex items-center gap-1 font-semibold text-blue-700 bg-blue-100/70 px-1.5 py-0.5 rounded text-[10px]">
                            📁 ${escapeHtml(item.category_name)}
                        </span>
                        <span>Periode: ${escapeHtml(item.month_name)} ${escapeHtml(item.year)}</span>
                    </div>
                </div>
            `;
        });

        const headerTitle = isMultiple || items.length > 1 
            ? `🎯 Rencana ${items.length} Anggaran Kebutuhan`
            : `🎯 Rencana Anggaran Kebutuhan`;

        cardEl.innerHTML = `
            <div class="space-y-2.5">
                <div class="flex items-center justify-between pb-1.5 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-800">${headerTitle}</span>
                    <span class="text-xs font-extrabold text-[#234066]">${escapeHtml(data.total_amount_formatted)}</span>
                </div>

                <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                    ${itemsHtml}
                </div>

                <p class="text-[11px] text-gray-400 italic">
                    Setiap anggaran pos ini otomatis terhubung ke kategori standar dan mengontrol pengeluaran riil Anda.
                </p>

                <hr class="border-gray-200 my-2">

                <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                    <button type="button" 
                            onclick="cancelBudget('${cardId}')"
                            class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 text-xs font-semibold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="button" 
                            onclick="confirmAndSaveBudgets('${cardId}')"
                            class="flex-1 px-4 py-2.5 bg-[#234066] hover:bg-[#1a304e] text-white text-xs font-bold rounded-xl shadow-sm transition-all text-center flex items-center justify-center gap-1.5 hover:scale-[1.01] active:scale-[0.99]">
                        <span>🎯 Simpan Anggaran (${items.length} Kebutuhan)</span>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(cardEl);
        container.scrollTop = container.scrollHeight;
    }

    function cancelBudget(cardId) {
        delete window.aiPendingBudgets[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);
        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="w-full text-center py-1.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-500">
                    ✕ Penetapan anggaran dibatalkan
                </div>
            `;
        }
        const messageContainer = document.getElementById('ai-chat-messages');
        renderBotTextReply('Baik, penetapan anggaran dibatalkan. Ada yang ingin diatur kembali?', messageContainer);
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    async function confirmAndSaveBudgets(cardId) {
        const data = window.aiPendingBudgets[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);

        if (!data || !data.items || data.items.length === 0) {
            alert('Data anggaran tidak ditemukan.');
            return;
        }

        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="w-full text-center py-2 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-[#234066] flex items-center justify-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 rounded-full border-2 border-[#234066] border-t-transparent animate-spin"></span>
                    <span>Menyimpan anggaran...</span>
                </div>
            `;
        }

        const payload = data.items.length === 1
            ? {
                name: data.items[0].name || data.items[0].category_name,
                category_id: data.items[0].category_id,
                amount: data.items[0].amount,
                month: data.items[0].month,
                year: data.items[0].year,
            }
            : {
                items: data.items.map(it => ({
                    name: it.name || it.category_name,
                    category_id: it.category_id,
                    amount: it.amount,
                    month: it.month,
                    year: it.year,
                }))
            };

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/ai-chat/save-budget', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            const resData = await response.json();
            const messageContainer = document.getElementById('ai-chat-messages');

            if (response.ok) {
                delete window.aiPendingBudgets[cardId];
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-700 flex items-center justify-center gap-1.5">
                            <span>✓ Berhasil! Anggaran telah disimpan</span>
                        </div>
                    `;
                }
                renderBotSuccessReply(`<strong>Berhasil!</strong> ${escapeHtml(resData.message || 'Anggaran berhasil ditetapkan.')} Anda dapat memantau realisasinya di menu <a href="/anggaran" class="underline font-bold text-blue-700">Anggaran</a>.`, messageContainer);
            } else {
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                            ✕ Gagal: ${escapeHtml(resData.message || 'Terjadi kesalahan')}
                        </div>
                    `;
                }
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }

        const messageContainer = document.getElementById('ai-chat-messages');
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // Cancel Single Transaction
    function cancelTransaction(cardId) {
        delete window.aiPendingTransactions[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);
        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="w-full text-center py-1.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-500">
                    ✕ Transaksi dibatalkan (Tidak disimpan)
                </div>
            `;
        }
        const messageContainer = document.getElementById('ai-chat-messages');
        renderBotTextReply('Baik, transaksi dibatalkan dan <strong>tidak disimpan</strong> ke database. Ada transaksi lain yang ingin dicatat?', messageContainer);
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // Confirm Single Transaction
    async function confirmAndSaveTransaction(cardId) {
        const data = window.aiPendingTransactions[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);

        if (!data) {
            alert('Data transaksi tidak ditemukan atau sudah diproses.');
            return;
        }

        const descInput = document.getElementById('input-desc-' + cardId);
        const savingSelect = document.getElementById('select-saving-' + cardId);
        const categorySelect = document.getElementById('select-category-' + cardId);

        const description = (descInput && descInput.value.trim()) ? descInput.value.trim() : (data.description || 'Transaksi AI');
        const savingsId = savingSelect ? savingSelect.value : data.savings_id;
        const categoryId = categorySelect ? categorySelect.value : data.category_id;

        if (!savingsId) {
            alert('Silakan pilih rekening tabungan terlebih dahulu.');
            return;
        }

        const payload = {
            savings_id: parseInt(savingsId),
            category_id: categoryId ? parseInt(categoryId) : null,
            type: data.type,
            amount: parseFloat(data.amount),
            description: description,
            transaction_date: data.date
        };

        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="w-full text-center py-2 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-[#234066] flex items-center justify-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 rounded-full border-2 border-[#234066] border-t-transparent animate-spin"></span>
                    <span>Menyimpan ke database...</span>
                </div>
            `;
        }

        const messageContainer = document.getElementById('ai-chat-messages');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/ai-chat/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const resData = await response.json();
                delete window.aiPendingTransactions[cardId];

                if (descInput) descInput.disabled = true;
                if (savingSelect) savingSelect.disabled = true;
                if (categorySelect) categorySelect.disabled = true;

                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-700 flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>✓ Berhasil Disimpan ke Database</span>
                        </div>
                    `;
                }

                renderBotSuccessReply(`
                    <strong>Transaksi Berhasil Disimpan!</strong><br>
                    ${escapeHtml(resData.message)}<br>
                    <span class="text-xs text-emerald-800 font-medium">Saldo terkini: <strong>${escapeHtml(resData.formatted_balance || '')}</strong></span>
                `, messageContainer);

                // Auto update saldo di UI jika elemen ada
                const balanceDisplay = document.querySelector('.balance-amount');
                if (balanceDisplay && resData.formatted_balance && !balanceDisplay.textContent.includes('••••••••')) {
                    balanceDisplay.textContent = resData.formatted_balance;
                }
            } else {
                const errData = await response.json().catch(() => ({}));
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                            ✕ Gagal: ${escapeHtml(errData.message || 'Terjadi kesalahan sistem')}
                        </div>
                    `;
                }
            }
        } catch (err) {
            if (actionsContainer) {
                actionsContainer.innerHTML = `
                    <div class="w-full text-center py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                        ✕ Gagal menyimpan. Silakan coba kembali.
                    </div>
                `;
            }
        }

        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    function formatChatText(text) {
        if (!text) return '';
        let res = escapeHtml(text);
        res = res
            .replace(/&lt;strong&gt;/gi, '<strong class="font-bold text-gray-900">')
            .replace(/&lt;\/strong&gt;/gi, '</strong>')
            .replace(/&lt;b&gt;/gi, '<strong class="font-bold text-gray-900">')
            .replace(/&lt;\/b&gt;/gi, '</strong>')
            .replace(/&lt;em&gt;/gi, '<em>')
            .replace(/&lt;\/em&gt;/gi, '</em>')
            .replace(/&lt;br\s*\/?&gt;/gi, '<br>');

        res = res.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-gray-900">$1</strong>');
        res = res.replace(/(?:^|\n)[-*]\s+(.*?)(?=\n|$)/g, '<div class="flex items-start gap-1.5 pl-1 my-0.5"><span class="text-indigo-500 font-bold mt-0.5">•</span><span>$1</span></div>');
        res = res.replace(/\n/g, '<br>');
        return res;
    }

    function renderBotTextReply(text, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start animate-fade-in';
        const formattedContent = formatChatText(text);

        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#1b3252] to-[#2e5283] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="4" r="1.5" />
                    <rect x="5" y="7" width="14" height="11" rx="4" />
                </svg>
            </div>
            <div class="bg-white text-gray-800 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[85%] border border-gray-200/70 shadow-xs leading-relaxed break-words space-y-1">
                ${formattedContent}
            </div>
        `;
        container.appendChild(replyEl);
    }

    function renderBotSuccessReply(html, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start animate-fade-in';
        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="bg-emerald-50/90 text-emerald-950 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[85%] border border-emerald-200/80 shadow-xs leading-relaxed break-words space-y-1">
                ${html}
            </div>
        `;
        container.appendChild(replyEl);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function handleAiImageSelect(fileInput) {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;

        const messageContainer = document.getElementById('ai-chat-messages');
        const textInput = document.getElementById('ai-chatbot-input');
        const caption = textInput ? textInput.value.trim() : '';
        if (!messageContainer) return;

        document.getElementById('ai-quick-suggestions')?.classList.add('hidden');

        // Preview image bubble from user
        const previewUrl = URL.createObjectURL(file);
        const userBubble = document.createElement('div');
        userBubble.className = 'flex justify-end animate-fade-in';
        userBubble.innerHTML = `
            <div class="bg-[#234066] text-white p-2 rounded-2xl rounded-tr-xs max-w-[78%] shadow-xs space-y-1.5">
                <img src="${previewUrl}" alt="Uploaded receipt" class="rounded-xl max-h-48 w-auto object-contain bg-black/10">
                ${caption ? `<p class="text-xs px-1.5 pb-1">${escapeHtml(caption)}</p>` : '<p class="text-[10px] text-blue-100 px-1.5">📷 Mengirim gambar untuk deteksi OCR...</p>'}
            </div>
        `;
        messageContainer.appendChild(userBubble);

        if (textInput) {
            textInput.value = '';
            textInput.style.height = 'auto';
        }
        fileInput.value = '';
        messageContainer.scrollTop = messageContainer.scrollHeight;

        const loadingId = 'ai-ocr-loading-' + Date.now();
        const loadingEl = document.createElement('div');
        loadingEl.id = loadingId;
        loadingEl.className = 'flex items-center gap-2 text-xs text-gray-400 p-2 italic';
        loadingEl.innerHTML = `
            <span class="inline-block w-2 h-2 rounded-full bg-indigo-600 animate-bounce"></span>
            <span class="inline-block w-2 h-2 rounded-full bg-indigo-600 animate-bounce [animation-delay:0.2s]"></span>
            <span>AI sedang mendeteksi kategori gambar & nominal...</span>
        `;
        messageContainer.appendChild(loadingEl);
        messageContainer.scrollTop = messageContainer.scrollHeight;

        try {
            const formData = new FormData();
            formData.append('image', file);
            if (caption) formData.append('caption', caption);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/ai-chat/parse-image', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: formData
            });

            document.getElementById(loadingId)?.remove();

            if (response.ok) {
                const resData = await response.json();
                if (resData.intent === 'RECORD_TRANSACTION' && resData.data) {
                    const badgeHtml = `<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-1">🏷️ Kategori Terdeteksi: ${escapeHtml(resData.image_category_label || 'Dokumen Keuangan')}</div>`;
                    const badgeWrap = document.createElement('div');
                    badgeWrap.innerHTML = badgeHtml;
                    messageContainer.appendChild(badgeWrap);

                    renderConfirmationCard(resData.data, resData.reply, messageContainer, resData.is_multiple);
                } else {
                    renderBotTextReply(resData.reply || 'AI tidak dapat memproses gambar ini karena bukan termasuk kategori dokumen keuangan.', messageContainer);
                }
            } else {
                const errData = await response.json().catch(() => ({}));
                renderBotTextReply(errData.message || 'Gagal memproses gambar. Pastikan format JPG/PNG/WEBP dan ukuran maksimal 5MB.', messageContainer);
            }
        } catch (err) {
            document.getElementById(loadingId)?.remove();
            renderBotTextReply('Koneksi terputus saat mengunggah gambar.', messageContainer);
        }

        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // =========================================================================
    // VOICE CONVERSATION MODE, LIVE TRANSCRIPTOR & PUSH-TO-TEXT ENGINE
    // =========================================================================
    window.aiVoiceMode = false;
    window.aiTtsEnabled = true;
    window.aiRecognition = null;
    window.aiIsListening = false;
    window.aiVoiceAutoSubmit = false;
    window.aiFinalTranscript = '';

    function getSpeechRecognitionClass() {
        return window.SpeechRecognition || window.webkitSpeechRecognition || null;
    }

    function toggleVoiceMode() {
        const panel = document.getElementById('ai-voice-panel');
        const btn = document.getElementById('ai-voice-mode-btn');
        if (!panel) return;

        window.aiVoiceMode = !window.aiVoiceMode;
        if (window.aiVoiceMode) {
            panel.classList.remove('hidden');
            if (btn) btn.classList.add('bg-emerald-500/30', 'text-emerald-200');
            if (!getSpeechRecognitionClass()) {
                const tEl = document.getElementById('ai-voice-transcript');
                if (tEl) tEl.textContent = 'Browser ini belum mendukung Speech-to-Text. Gunakan Chrome/Edge/Safari untuk fitur suara.';
            }
        } else {
            panel.classList.add('hidden');
            if (btn) btn.classList.remove('bg-emerald-500/30', 'text-emerald-200');
            stopPushToTalk(false);
            stopAiSpeech();
        }
    }

    function toggleAiTts() {
        window.aiTtsEnabled = !window.aiTtsEnabled;
        const btn = document.getElementById('ai-tts-toggle-btn');
        if (btn) {
            btn.textContent = window.aiTtsEnabled ? '🔊 Suara AI: ON' : '🔇 Suara AI: OFF';
        }
        if (!window.aiTtsEnabled) {
            stopAiSpeech();
        }
    }

    function togglePushToText() {
        if (window.aiIsListening) {
            stopPushToTalk(false);
        } else {
            startPushToTalk(false);
        }
    }

    function startPushToTalk(autoSubmitOnStop) {
        const SpeechRec = getSpeechRecognitionClass();
        if (!SpeechRec) {
            alert('Browser Anda belum mendukung fitur dikte suara (Web Speech API). Gunakan Google Chrome, Edge, atau Safari.');
            return;
        }
        if (window.aiIsListening) return;

        stopAiSpeech();
        window.aiVoiceAutoSubmit = !!autoSubmitOnStop;
        window.aiFinalTranscript = '';

        const recognition = new SpeechRec();
        recognition.lang = 'id-ID';
        recognition.interimResults = true;
        recognition.continuous = true;

        const input = document.getElementById('ai-chatbot-input');
        const micBtn = document.getElementById('ai-mic-btn');
        const pttBtn = document.getElementById('ai-ptt-hold-btn');
        const pttLabel = document.getElementById('ai-ptt-label');
        const transcriptEl = document.getElementById('ai-voice-transcript');
        const statusDot = document.getElementById('ai-voice-status-dot');
        const statusText = document.getElementById('ai-voice-status-text');

        recognition.onstart = function() {
            window.aiIsListening = true;
            if (micBtn) micBtn.classList.add('bg-rose-100', 'text-rose-600', 'animate-pulse');
            if (pttBtn) pttBtn.classList.add('from-rose-500', 'to-red-600', 'scale-105');
            if (pttLabel) pttLabel.textContent = 'Mendengarkan... Lepas untuk Kirim';
            if (statusDot) statusDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-400 animate-ping';
            if (statusText) statusText.textContent = 'Mendengarkan suara Anda...';
            if (transcriptEl) transcriptEl.textContent = 'Silakan berbicara...';
        };

        recognition.onresult = function(event) {
            let interim = '';
            let finalPart = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const text = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalPart += text + ' ';
                } else {
                    interim += text;
                }
            }
            if (finalPart) {
                window.aiFinalTranscript += finalPart;
            }
            const combined = (window.aiFinalTranscript + interim).trim();
            if (transcriptEl && combined) {
                transcriptEl.textContent = combined;
            }
            if (input && combined) {
                input.value = combined;
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 128) + 'px';
            }
        };

        recognition.onerror = function(event) {
            if (transcriptEl && event.error !== 'aborted') {
                transcriptEl.textContent = 'Gagal menangkap suara (' + event.error + '). Pastikan izin mikrofon aktif.';
            }
            resetMicVisuals();
        };

        recognition.onend = function() {
            const shouldSubmit = window.aiVoiceAutoSubmit;
            resetMicVisuals();
            if (shouldSubmit && input && input.value.trim()) {
                handleAiChatSubmit();
            }
        };

        window.aiRecognition = recognition;
        try {
            recognition.start();
        } catch (e) {
            resetMicVisuals();
        }
    }

    function stopPushToTalk(autoSubmit) {
        if (!window.aiIsListening || !window.aiRecognition) return;
        if (typeof autoSubmit === 'boolean') {
            window.aiVoiceAutoSubmit = autoSubmit;
        }
        try {
            window.aiRecognition.stop();
        } catch (e) {
            resetMicVisuals();
        }
    }

    function resetMicVisuals() {
        window.aiIsListening = false;
        const micBtn = document.getElementById('ai-mic-btn');
        const pttBtn = document.getElementById('ai-ptt-hold-btn');
        const pttLabel = document.getElementById('ai-ptt-label');
        const statusDot = document.getElementById('ai-voice-status-dot');
        const statusText = document.getElementById('ai-voice-status-text');

        if (micBtn) micBtn.classList.remove('bg-rose-100', 'text-rose-600', 'animate-pulse');
        if (pttBtn) pttBtn.classList.remove('from-rose-500', 'to-red-600', 'scale-105');
        if (pttLabel) pttLabel.textContent = 'Tekan & Tahan untuk Bicara';
        if (statusDot) statusDot.className = 'w-2 h-2 rounded-full bg-emerald-400';
        if (statusText) statusText.textContent = 'Mode Ngobrol Aktif • Siap Mendengarkan';
    }

    function speakAiText(rawText) {
        if (!('speechSynthesis' in window) || !rawText) return;
        stopAiSpeech();

        // Bersihkan simbol markdown/emoji ringan agar suara terdengar natural
        const cleanText = rawText
            .replace(/\*\*(.*?)\*\*/g, '$1')
            .replace(/<[^>]*>/g, ' ')
            .replace(/[#•🩺💡🎯🔥💰📊⚠️✅💳🏷️]/g, '')
            .replace(/\s+/g, ' ')
            .trim();

        if (!cleanText) return;

        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'id-ID';
        utterance.rate = 1.03;

        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => v.lang && v.lang.toLowerCase().startsWith('id'));
        if (idVoice) utterance.voice = idVoice;

        const statusText = document.getElementById('ai-voice-status-text');
        utterance.onstart = () => {
            if (statusText) statusText.textContent = 'Finansia Advisor sedang berbicara...';
        };
        utterance.onend = () => {
            if (statusText && window.aiVoiceMode) statusText.textContent = 'Mode Ngobrol Aktif • Siap Mendengarkan';
        };

        window.speechSynthesis.speak(utterance);
    }

    function stopAiSpeech() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
    }

    // =========================================================================
    // AI FUTURE PLAN GENERATOR CARD
    // =========================================================================
    window.aiPendingFuturePlans = window.aiPendingFuturePlans || {};

    function renderFuturePlanConfirmationCard(data, replyText, container) {
        if (replyText) {
            renderBotTextReply(replyText, container);
        }

        const cardId = 'card-fp-' + Date.now();
        window.aiPendingFuturePlans[cardId] = data;

        const itemsHtml = (data.items || []).map((it, idx) => `
            <div class="p-2.5 bg-gray-50 border border-gray-100 rounded-xl space-y-1 text-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-gray-800">#${idx + 1} ${escapeHtml(it.title)}</span>
                    <span class="font-extrabold text-indigo-700 shrink-0">${escapeHtml(it.subtotal_formatted)}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-gray-500">
                    <span>📅 ${escapeHtml(it.start_date || '-')} s/d ${escapeHtml(it.end_date || '-')}</span>
                    <span>Qty: ${it.quantity}</span>
                </div>
            </div>
        `).join('');

        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-indigo-200 rounded-2xl p-4 shadow-sm space-y-3';
        cardEl.innerHTML = `
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2 pb-2 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">🗓️ Draft Future Plan AI</span>
                        <h4 class="text-sm font-bold text-gray-900">${escapeHtml(data.title)}</h4>
                        <p class="text-[11px] text-gray-500">Target: ${escapeHtml(data.target_date || '-')}</p>
                    </div>
                    <span class="text-xs font-extrabold text-indigo-700 shrink-0">${escapeHtml(data.total_estimated_formatted)}</span>
                </div>

                <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                    ${itemsHtml}
                </div>

                <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                    <button type="button" onclick="document.getElementById('${cardId}')?.remove()"
                            class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="button" onclick="confirmAndSaveFuturePlan('${cardId}')"
                            class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        ✓ Simpan Future Plan (${(data.items || []).length} Item)
                    </button>
                </div>
            </div>
        `;
        container.appendChild(cardEl);
        container.scrollTop = container.scrollHeight;
    }

    async function confirmAndSaveFuturePlan(cardId) {
        const data = window.aiPendingFuturePlans[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);
        if (!data) return;

        if (actionsContainer) {
            actionsContainer.innerHTML = '<div class="w-full text-center py-2 text-xs font-semibold text-indigo-700">Menyimpan Future Plan & jadwal Gantt Chart...</div>';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/ai-chat/save-future-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    title: data.title,
                    description: data.description,
                    target_date: data.target_date,
                    items: data.items
                })
            });

            const resData = await response.json();
            if (response.ok && resData.status === 'success') {
                delete window.aiPendingFuturePlans[cardId];
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <a href="${escapeHtml(resData.redirect_url)}" class="w-full text-center py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition block">
                            ✓ Tersimpan! Buka Future Plan & Gantt Chart →
                        </a>
                    `;
                }
            } else {
                if (actionsContainer) {
                    actionsContainer.innerHTML = `<div class="text-xs text-rose-600 font-semibold">${escapeHtml(resData.message || 'Gagal menyimpan Future Plan.')}</div>`;
                }
            }
        } catch (err) {
            if (actionsContainer) {
                actionsContainer.innerHTML = '<div class="text-xs text-rose-600 font-semibold">Koneksi gagal saat menyimpan.</div>';
            }
        }
    }
</script>
