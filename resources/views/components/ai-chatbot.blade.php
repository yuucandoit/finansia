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
         class="hidden w-[calc(100vw-24px)] sm:w-[430px] max-w-[430px] max-h-[85vh] sm:max-h-[650px] bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-200/90 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right">
        
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-[#1c3454] to-[#234066] px-4 sm:px-5 py-3.5 flex items-center justify-between text-white select-none shadow-sm">
            <div class="flex items-center gap-3">
                <!-- Bot Avatar Icon -->
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center shadow-xs flex-shrink-0 border border-white/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <path d="M12 5.5v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                        <circle cx="9" cy="12" r="1.8" fill="white" />
                        <circle cx="15" cy="12" r="1.8" fill="white" />
                        <circle cx="9" cy="12" r="0.8" fill="#234066" />
                        <circle cx="15" cy="12" r="0.8" fill="#234066" />
                        <path d="M10 15h4" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base tracking-tight text-white leading-tight">Asisten Finansial AI</h3>
                    <p class="text-[10px] sm:text-[11px] text-blue-200/90 flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                        Online • Siap mencatat & mengatur
                    </p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-1">
                <button type="button" 
                        onclick="resetAiChat()" 
                        class="text-white/70 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors"
                        title="Mulai percakapan baru / Reset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
                <button type="button" 
                        onclick="toggleAiChatbot()" 
                        class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors"
                        title="Tutup / Minimize">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>
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
                </div>
            </div>

        </div>

        <!-- Chat Input Form -->
        <div class="p-3 sm:p-3.5 bg-white border-t border-gray-100">
            <form id="ai-chatbot-form" onsubmit="handleAiChatSubmit(event)" class="relative flex items-end gap-2 bg-gray-50 border border-gray-200 rounded-2xl p-1.5 focus-within:border-[#234066] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#234066]/10 transition-all">
                <textarea 
                       id="ai-chatbot-input" 
                       rows="1"
                       placeholder="Ketik transaksi atau anggaran..." 
                       class="flex-1 bg-transparent border-0 px-2.5 py-1.5 text-sm text-gray-800 outline-none placeholder:text-gray-400 resize-none max-h-32 min-h-[38px] leading-relaxed scroll-smooth"></textarea>
                
                <button type="submit" 
                        id="ai-chatbot-send-btn"
                        class="w-9 h-9 text-white bg-[#234066] hover:bg-[#1a304e] rounded-xl transition-all flex items-center justify-center flex-shrink-0 disabled:opacity-40 shadow-xs active:scale-95"
                        title="Kirim pesan (Ctrl+Enter)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 rotate-45 -translate-y-0.5 translate-x-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
            <div class="flex items-center justify-between px-2 mt-1.5 text-[10px] text-gray-400 select-none">
                <span><kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Enter</kbd> baris baru</span>
                <span><kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Ctrl</kbd>+<kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px] font-sans text-gray-500">Enter</kbd> kirim</span>
            </div>
        </div>

    </div>

    <!-- Floating Trigger Button -->
    <button id="ai-chatbot-trigger" 
            type="button"
            onclick="toggleAiChatbot()" 
            class="bg-[#234066] hover:bg-[#1a304e] text-white p-3 sm:p-3.5 rounded-full shadow-2xl hover:shadow-blue-900/30 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2.5 group">
        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center flex-shrink-0 shadow-xs">
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
        </div>
        <span class="text-sm font-bold pr-1">Asisten AI</span>
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

        // Enter key adds new line (default), Ctrl+Enter or Cmd+Enter submits
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    handleAiChatSubmit(e);
                }
                // Jika hanya Enter biasa: browser secara otomatis membuat baris baru (\n) ke bawah tanpa submit form!
            }
        });
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
                </div>
            </div>
        `;

        window.aiPendingTransactions = {};
        window.aiPendingBudgets = {};
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
            const response = await fetch('/ai-chat/parse', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ message: userText })
            });

            document.getElementById(loadingId)?.remove();
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();

            if (response.ok) {
                const resData = await response.json();
                if (resData.intent === 'RECORD_BUDGET' && resData.data) {
                    renderBudgetConfirmationCard(resData.data, resData.reply, messageContainer, resData.is_multiple);
                } else if (resData.intent === 'RECORD_TRANSACTION' && resData.data) {
                    renderConfirmationCard(resData.data, resData.reply, messageContainer, resData.is_multiple);
                } else {
                    renderBotTextReply(resData.reply || 'Pesan Anda telah diterima.', messageContainer);
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
                        <span class="font-extrabold ${isExpense ? 'text-[#234066]' : 'text-emerald-600'}">${escapeHtml(item.amount_formatted)}</span>
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
</script>
