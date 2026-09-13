<div id="ai-chatbot-container" class="fixed bottom-4 sm:bottom-6 right-3 sm:right-6 z-50 flex flex-col items-end font-sans max-w-[calc(100vw-24px)]">

    <!-- Chat Window Card -->
    <div id="ai-chatbot-window" 
         class="hidden w-[calc(100vw-24px)] sm:w-[390px] max-w-[390px] max-h-[85vh] sm:max-h-[620px] bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right">
        
        <!-- Chat Header -->
        <div class="bg-[#234066] px-4 sm:px-5 py-3.5 sm:py-4 flex items-center justify-between text-white select-none shadow-sm">
            <div class="flex items-center gap-3">
                <!-- Bot Avatar Icon -->
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#234066]" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <path d="M12 5.5v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                        <circle cx="9" cy="12" r="1.8" fill="white" />
                        <circle cx="15" cy="12" r="1.8" fill="white" />
                        <circle cx="9" cy="12" r="0.8" fill="#234066" />
                        <circle cx="15" cy="12" r="0.8" fill="#234066" />
                        <path d="M10 15h4" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                        <!-- Antenna ears -->
                        <path d="M3.5 11a1.5 1.5 0 0 1 1.5-1.5v5A1.5 1.5 0 0 1 3.5 13v-2zM19 9.5a1.5 1.5 0 0 1 1.5 1.5v2a1.5 1.5 0 0 1-1.5 1.5v-5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base tracking-wide text-white">Asisten AI - Finansia</h3>
                    <p class="text-[10px] sm:text-[11px] text-blue-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                        Online • Siap mencatat
                    </p>
                </div>
            </div>

            <!-- Header Actions -->
            <button type="button" 
                    onclick="toggleAiChatbot()" 
                    class="text-white/80 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors"
                    title="Tutup / Minimize">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>

        <!-- Chat Messages Container -->
        <div id="ai-chat-messages" class="p-3 sm:p-4 space-y-3 sm:space-y-4 bg-slate-50/50 flex-1 max-h-[50vh] sm:max-h-[440px] min-h-[260px] overflow-y-auto scroll-smooth">
            
            
            <div class="flex justify-start gap-2.5 items-start">
                <div class="w-7 h-7 rounded-full bg-[#234066] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                    </svg>
                </div>
                <div class="bg-white text-gray-800 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[85%] leading-relaxed border border-gray-200/70 shadow-xs">
                    <p class="font-semibold text-gray-900">Halo! Ada yang bisa saya bantu catat hari ini? 😊</p>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Anda bisa ketik pengeluaran atau pemasukan secara santai, contoh:
                    </p>
                    <div class="mt-2 space-y-1 text-xs text-blue-700 font-medium">
                        <div class="cursor-pointer hover:underline bg-blue-50/70 px-2.5 py-1.5 rounded-lg transition" onclick="quickFillInput(this.innerText)">
                            "Bulan ini jajan makan udah habis berapa?"
                        </div>
                        <div class="cursor-pointer hover:underline bg-blue-50/70 px-2.5 py-1.5 rounded-lg transition" onclick="quickFillInput(this.innerText)">
                            "Aku abis beli kopi seharga 25ribu"
                        </div>
                        <div class="cursor-pointer hover:underline bg-blue-50/70 px-2.5 py-1.5 rounded-lg transition" onclick="quickFillInput(this.innerText)">
                            "Barusan makan ramen 45rb pake GoPay"
                        </div>
                        <div class="cursor-pointer hover:underline bg-blue-50/70 px-2.5 py-1.5 rounded-lg transition" onclick="quickFillInput(this.innerText)">
                            "Gaji masuk 5jt ke Mandiri"
                        </div>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="p-3 bg-white border-t border-gray-100">
            <form onsubmit="handleAiChatSubmit(event)" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" 
                           id="ai-chatbot-input" 
                           placeholder="Ketik transaksi Anda di sini..." 
                           autocomplete="off"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-[#234066]/20 focus:border-[#234066] transition-all bg-white placeholder:text-gray-400" />
                </div>
                <button type="submit" 
                        id="ai-chatbot-send-btn"
                        class="p-2 text-[#234066] hover:text-[#182c47] hover:bg-blue-50 rounded-xl transition-all flex items-center justify-center flex-shrink-0 disabled:opacity-50"
                        title="Kirim pesan">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6 rotate-45 -translate-y-0.5 translate-x-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>

    </div>

    
    <button id="ai-chatbot-trigger" 
            type="button"
            onclick="toggleAiChatbot()" 
            class="bg-[#234066] hover:bg-[#1a304e] text-white p-3.5 rounded-full shadow-2xl hover:shadow-blue-900/30 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2.5 group">
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

    function toggleAiChatbot() {
        const chatWindow = document.getElementById('ai-chatbot-window');
        const triggerBtn = document.getElementById('ai-chatbot-trigger');

        if (!chatWindow || !triggerBtn) return;

        if (chatWindow.classList.contains('hidden')) {
            chatWindow.classList.remove('hidden');
            triggerBtn.classList.add('hidden');
            const input = document.getElementById('ai-chatbot-input');
            if (input) input.focus();
        } else {
            chatWindow.classList.add('hidden');
            triggerBtn.classList.remove('hidden');
        }
    }

    function quickFillInput(text) {
        const input = document.getElementById('ai-chatbot-input');
        if (input) {
            input.value = text.replace(/["']/g, '');
            input.focus();
        }
    }

    async function handleAiChatSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('ai-chatbot-input');
        const sendBtn = document.getElementById('ai-chatbot-send-btn');
        const messageContainer = document.getElementById('ai-chat-messages');

        if (!input || !input.value.trim() || !messageContainer) return;

        const userText = input.value.trim();

        const userBubble = document.createElement('div');
        userBubble.className = 'flex justify-end';
        userBubble.innerHTML = `
            <div class="bg-[#eef5fc] text-gray-800 text-sm font-medium px-4 py-3 rounded-2xl rounded-tr-xs max-w-[85%] border border-blue-100 shadow-2xs leading-relaxed">
                You: "${escapeHtml(userText)}"
            </div>
        `;
        messageContainer.appendChild(userBubble);
        input.value = '';
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
            <span>Asisten AI sedang menganalisis transaksi...</span>
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
                if (resData.intent === 'RECORD_TRANSACTION' && resData.data) {
                    renderConfirmationCard(resData.data, resData.reply, messageContainer);
                } else {
                    renderBotTextReply(resData.reply || 'Pesan Anda telah diterima.', messageContainer);
                }
            } else {
                const errData = await response.json().catch(() => ({}));
                renderBotTextReply(errData.reply || 'Maaf, terjadi kendala saat memproses permintaan. Coba ulangi kembali ya.', messageContainer);
            }
        } catch (err) {
            document.getElementById(loadingId)?.remove();
            input.disabled = false;
            sendBtn.disabled = false;
            renderBotTextReply('Koneksi terputus. Silakan periksa jaringan Anda.', messageContainer);
        }

        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    function renderConfirmationCard(data, replyText, container) {
        if (replyText) {
            renderBotTextReply(replyText, container);
        }

        const cardId = 'card-' + Date.now();
        window.aiPendingTransactions[cardId] = data;

        const isExpense = data.type !== 'Cash In';
        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3';

        let savingsOptions = '';
        if (data.available_savings && data.available_savings.length > 0) {
            savingsOptions = data.available_savings.map(s => {
                const balFormatted = s.balance_formatted || ('Rp ' + Number(s.balance || 0).toLocaleString('id-ID'));
                return `<option value="${s.id}" data-balance="${s.balance || 0}" ${s.id === data.savings_id ? 'selected' : ''}>${escapeHtml(s.name)} (Saldo: ${balFormatted})</option>`;
            }).join('');
        } else {
            const initialBalFormatted = data.saving_balance_formatted || ('Rp ' + Number(data.saving_balance || 0).toLocaleString('id-ID'));
            savingsOptions = `<option value="${data.savings_id}" data-balance="${data.saving_balance || 0}" selected>${escapeHtml(data.account_name)} (Saldo: ${initialBalFormatted})</option>`;
        }

        let categoryOptions = '';
        if (data.available_categories && data.available_categories.length > 0) {
            categoryOptions = data.available_categories.map(c => 
                `<option value="${c.id}" ${c.id === data.category_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
            ).join('');
        } else {
            categoryOptions = `<option value="${data.category_id}" selected>${escapeHtml(data.category_name)}</option>`;
        }

        const selectedSaving = (data.available_savings || []).find(s => s.id === data.savings_id);
        const currentBal = selectedSaving ? Number(selectedSaving.balance) : Number(data.saving_balance || 0);
        const isInsufficient = isExpense && currentBal < Number(data.amount);

        cardEl.innerHTML = `
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Jenis:</span>
                    <span class="text-gray-800 font-medium">${escapeHtml(data.type_label || data.type)}</span>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Deskripsi:</span>
                    <input type="text" id="input-desc-${cardId}" value="${escapeHtml(data.description || '')}" 
                           placeholder="Deskripsi transaksi..."
                           class="flex-1 text-sm text-gray-800 font-semibold bg-gray-50 border border-gray-200 rounded-lg py-1 px-2.5 outline-none focus:ring-1 focus:ring-[#234066] focus:border-[#234066]" />
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Nominal:</span>
                    <span class="text-gray-900 font-bold text-base text-[#234066]">${escapeHtml(data.amount_formatted)}</span>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Rekening:</span>
                    <select id="select-saving-${cardId}" 
                            class="flex-1 text-sm text-gray-800 font-semibold bg-gray-50 border border-gray-200 rounded-lg py-1 px-2.5 outline-none focus:ring-1 focus:ring-[#234066] focus:border-[#234066]">
                        ${savingsOptions}
                    </select>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Kategori:</span>
                    <select id="select-category-${cardId}" 
                            class="flex-1 text-sm text-gray-800 font-medium bg-gray-50 border border-gray-200 rounded-lg py-1 px-2.5 outline-none focus:ring-1 focus:ring-[#234066] focus:border-[#234066]">
                        ${categoryOptions}
                    </select>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-gray-900 font-bold">Tanggal:</span>
                    <span class="text-gray-800 font-medium">${escapeHtml(data.date_formatted || data.date)}</span>
                </div>
            </div>

            <div id="balance-warning-${cardId}" class="${isInsufficient ? 'flex' : 'hidden'} items-start gap-2 p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span id="balance-warning-text-${cardId}">Saldo rekening ini (Rp ${currentBal.toLocaleString('id-ID')}) tidak mencukupi untuk transaksi sebesar ${escapeHtml(data.amount_formatted)}.</span>
            </div>

            <hr class="border-gray-200 my-2">

            <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                <button type="button" 
                        onclick="cancelTransaction('${cardId}')"
                        class="px-3.5 py-2 bg-white border border-gray-400 hover:border-gray-600 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-2xs">
                    [Batal]
                </button>
                <button type="button" 
                        id="btn-confirm-${cardId}"
                        onclick="confirmAndSaveTransaction('${cardId}')"
                        ${isInsufficient ? 'disabled' : ''}
                        class="flex-1 px-4 py-2 bg-[#234066] hover:bg-[#1a304e] text-white text-xs font-semibold rounded-xl shadow-md transition-all text-center flex items-center justify-center gap-1.5 ${isInsufficient ? 'opacity-50 cursor-not-allowed' : ''}">
                    <span>[Konfirmasi & Simpan]</span>
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

                    if (isExpense && optBal < parseFloat(data.amount)) {
                        if (warnEl) warnEl.classList.remove('hidden');
                        if (warnEl) warnEl.classList.add('flex');
                        if (warnTextEl) warnTextEl.textContent = `Saldo rekening ini (Rp ${optBal.toLocaleString('id-ID')}) tidak mencukupi untuk transaksi sebesar ${data.amount_formatted}.`;
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
        renderBotTextReply('Baik, transaksi dibatalkan dan **tidak disimpan** ke database. Ada transaksi lain yang ingin dicatat?', messageContainer);
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

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

                // Disable inputs & dropdowns
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

                const selectedSavingOption = savingSelect ? savingSelect.options[savingSelect.selectedIndex] : null;
                const accountName = selectedSavingOption ? selectedSavingOption.text.split(' (Saldo:')[0] : data.account_name;
                const cleanDesc = escapeHtml(description);
                const cleanAcc = escapeHtml(accountName);

                const successHtml = `
                    <div class="font-bold text-emerald-800 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 inline flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Transaksi Berhasil Disimpan!</span>
                    </div>
                    <div class="text-xs text-emerald-900 mt-1.5 space-y-1">
                        <div><span class="text-emerald-700 font-medium">Deskripsi:</span> <span class="font-bold text-gray-900">${cleanDesc}</span></div>
                        <div><span class="text-emerald-700 font-medium">Nominal:</span> <span class="font-bold text-emerald-950">${escapeHtml(data.amount_formatted)}</span></div>
                        <div><span class="text-emerald-700 font-medium">Rekening:</span> <span class="font-bold text-gray-900">${cleanAcc}</span></div>
                        <div><span class="text-emerald-700 font-medium">Saldo Baru:</span> <span class="font-bold text-emerald-950">${escapeHtml(resData.formatted_balance)}</span></div>
                    </div>
                `;
                renderBotSuccessReply(successHtml, messageContainer);

                setTimeout(() => {
                    if (window.location.pathname.includes('dashboard') || window.location.pathname.includes('transaksi')) {
                        window.location.reload();
                    }
                }, 1800);

            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMsg = errorData.message || 'Terjadi kesalahan saat memproses data.';
                
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-2 px-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                            ✕ ${escapeHtml(errorMsg)}
                        </div>
                    `;
                }
                renderBotTextReply(`⚠️ **Peringatan**: ${errorMsg}`, messageContainer);
            }
        } catch (err) {
            if (actionsContainer) {
                actionsContainer.innerHTML = `
                    <div class="w-full text-center py-1.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                        Gagal koneksi ke server saat menyimpan data.
                    </div>
                `;
            }
        }

        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    function formatChatText(text) {
        if (!text) return '';
        // 1. Amankan karakter berbahaya
        let res = escapeHtml(text);

        // 2. Decode tag HTML aman yang mungkin dikirim oleh AI / server
        res = res
            .replace(/&lt;strong&gt;/gi, '<strong class="font-bold text-gray-900">')
            .replace(/&lt;\/strong&gt;/gi, '</strong>')
            .replace(/&lt;b&gt;/gi, '<strong class="font-bold text-gray-900">')
            .replace(/&lt;\/b&gt;/gi, '</strong>')
            .replace(/&lt;em&gt;/gi, '<em>')
            .replace(/&lt;\/em&gt;/gi, '</em>')
            .replace(/&lt;br\s*\/?&gt;/gi, '<br>');

        // 3. Konversi format markdown **bold** menjadi strong
        res = res.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-gray-900">$1</strong>');

        // 4. Ubah newlines menjadi <br>
        res = res.replace(/\n/g, '<br>');

        return res;
    }

    function renderBotTextReply(text, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start';

        const formattedContent = formatChatText(text);

        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-[#234066] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="4" r="1.5" />
                    <rect x="5" y="7" width="14" height="11" rx="4" />
                </svg>
            </div>
            <div class="bg-white text-gray-800 text-sm px-4 py-2.5 rounded-2xl rounded-tl-xs max-w-[85%] border border-gray-200/70 shadow-xs leading-relaxed">
                ${formattedContent}
            </div>
        `;
        container.appendChild(replyEl);
    }

    function renderBotSuccessReply(html, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start';
        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="bg-emerald-50/90 text-emerald-950 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[85%] border border-emerald-200/80 shadow-xs leading-relaxed">
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
