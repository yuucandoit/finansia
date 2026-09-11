<div id="ai-chatbot-container" class="fixed bottom-6 right-6 z-50 flex flex-col items-end font-sans">

    <!-- Floating Chatbot Window (Default: Hidden / Closed) -->
    <div id="ai-chatbot-window" 
         class="hidden w-[360px] sm:w-[390px] bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right">
        
        <!-- Header -->
        <div class="bg-[#234066] px-5 py-4 flex items-center justify-between text-white select-none shadow-sm">
            <div class="flex items-center gap-3">
                <!-- Bot Avatar Icon (White Circle with Robot Face) -->
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                    <svg class="w-6 h-6 text-[#234066]" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="1.5" />
                        <path d="M12 5.5v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <rect x="5" y="7" width="14" height="11" rx="4" />
                        <circle cx="9" cy="12" r="1.8" fill="white" />
                        <circle cx="15" cy="12" r="1.8" fill="white" />
                        <circle cx="9" cy="12" r="0.8" fill="#234066" />
                        <circle cx="15" cy="12" r="0.8" fill="#234066" />
                        <path d="M10 15h4" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                        <!-- Ears / Antennas -->
                        <path d="M3.5 11a1.5 1.5 0 0 1 1.5-1.5v5A1.5 1.5 0 0 1 3.5 13v-2zM19 9.5a1.5 1.5 0 0 1 1.5 1.5v2a1.5 1.5 0 0 1-1.5 1.5v-5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base tracking-wide text-white">Asisten AI - Finansia</h3>
                    <p class="text-[11px] text-blue-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                        Online • Siap mencatat
                    </p>
                </div>
            </div>

            <!-- Minimize / Close Button -->
            <button type="button" 
                    onclick="toggleAiChatbot()" 
                    class="text-white/80 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors"
                    title="Tutup / Minimize">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>

        <!-- Chat Content Area -->
        <div id="ai-chat-messages" class="p-4 space-y-4 bg-slate-50/50 max-h-[440px] min-h-[320px] overflow-y-auto scroll-smooth">
            
            <!-- Initial Greeting Message (How can I help you today?) -->
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

        <!-- Input Box Area -->
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
                    <!-- Telegram Paper Plane Outline SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6 rotate-45 -translate-y-0.5 translate-x-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>

    </div>

    <!-- Floating Action Button (Default: Shown when minimized) -->
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
    // Global storage for pending AI transactions (avoids inline JSON escaping bugs)
    window.aiPendingTransactions = window.aiPendingTransactions || {};

    // 1. Toggle Popup Chatbot
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

    // 2. Kirim pesan ke Backend AI
    async function handleAiChatSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('ai-chatbot-input');
        const sendBtn = document.getElementById('ai-chatbot-send-btn');
        const messageContainer = document.getElementById('ai-chat-messages');

        if (!input || !input.value.trim() || !messageContainer) return;

        const userText = input.value.trim();

        // Tampilkan bubble pesan pengguna
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

        // Tampilkan Loading Indicator
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

    // 3. Render Kartu Konfirmasi Transaksi
    function renderConfirmationCard(data, replyText, container) {
        const cardId = 'card-' + Date.now();
        
        // Simpan data ke objek global dengan key cardId agar tidak rusak saat di-pass
        window.aiPendingTransactions[cardId] = data;

        const cardEl = document.createElement('div');
        cardEl.id = cardId;
        cardEl.className = 'bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3';
        
        // Buat opsi dropdown rekening
        let savingsOptions = '';
        if (data.available_savings && data.available_savings.length > 0) {
            savingsOptions = data.available_savings.map(s => 
                `<option value="${s.id}" ${s.id === data.savings_id ? 'selected' : ''}>${escapeHtml(s.name)}</option>`
            ).join('');
        } else {
            savingsOptions = `<option value="${data.savings_id}" selected>${escapeHtml(data.account_name)}</option>`;
        }

        // Buat opsi dropdown kategori
        let categoryOptions = '';
        if (data.available_categories && data.available_categories.length > 0) {
            categoryOptions = data.available_categories.map(c => 
                `<option value="${c.id}" ${c.id === data.category_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
            ).join('');
        } else {
            categoryOptions = `<option value="${data.category_id}" selected>${escapeHtml(data.category_name)}</option>`;
        }

        cardEl.innerHTML = `
            <!-- Key-Value Row Details -->
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

            <!-- Separator -->
            <hr class="border-gray-200 my-2">

            <!-- Action Buttons for User Validation -->
            <div id="actions-${cardId}" class="flex items-center gap-2 pt-1">
                <button type="button" 
                        onclick="cancelTransaction('${cardId}')"
                        class="px-3.5 py-2 bg-white border border-gray-400 hover:border-gray-600 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-2xs">
                    [Batal]
                </button>
                <button type="button" 
                        onclick="confirmAndSaveTransaction('${cardId}')"
                        class="flex-1 px-4 py-2 bg-[#234066] hover:bg-[#1a304e] text-white text-xs font-semibold rounded-xl shadow-md transition-all text-center flex items-center justify-center gap-1.5">
                    <span>[Konfirmasi & Simpan]</span>
                </button>
            </div>
        `;
        container.appendChild(cardEl);
    }

    // 4. Aksi User: Batal (TIDAK Disimpan ke Database)
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

    // 5. Aksi User: Konfirmasi & Simpan (DISIMPAN ke Database)
    async function confirmAndSaveTransaction(cardId) {
        const data = window.aiPendingTransactions[cardId];
        const actionsContainer = document.getElementById('actions-' + cardId);

        if (!data) {
            alert('Data transaksi tidak ditemukan atau sudah diproses.');
            return;
        }

        // Ambil nilai terbaru jika user mengubah dropdown rekening/kategori atau deskripsi
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

                const accountName = savingSelect ? savingSelect.options[savingSelect.selectedIndex].text : data.account_name;
                renderBotSuccessReply(`✅ <strong>Transaksi Berhasil Disimpan!</strong><br>Deskripsi: <strong>${escapeHtml(description)}</strong><br>Nominal: <strong>${data.amount_formatted}</strong> dicatat ke rekening <strong>${escapeHtml(accountName)}</strong>.<br>Saldo baru: <strong>${resData.formatted_balance}</strong>.`, messageContainer);

                // Auto update / reload jika di dashboard
                setTimeout(() => {
                    if (window.location.pathname.includes('dashboard') || window.location.pathname.includes('transaksi')) {
                        window.location.reload();
                    }
                }, 1800);

            } else {
                const errorData = await response.json().catch(() => ({}));
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <div class="w-full text-center py-1.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                            Gagal menyimpan: ${escapeHtml(errorData.message || 'Terjadi kesalahan validasi')}
                        </div>
                    `;
                }
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

    function renderBotTextReply(text, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start';

        // Format: ubah bold markdown **teks** jadi <strong> dan newlines jadi <br>
        let formattedText = escapeHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-[#234066] text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="4" r="1.5" />
                    <rect x="5" y="7" width="14" height="11" rx="4" />
                </svg>
            </div>
            <div class="bg-white text-gray-800 text-sm px-4 py-2.5 rounded-2xl rounded-tl-xs max-w-[85%] border border-gray-200/70 shadow-xs leading-relaxed">
                ${formattedText}
            </div>
        `;
        container.appendChild(replyEl);
    }

    function renderBotSuccessReply(html, container) {
        const replyEl = document.createElement('div');
        replyEl.className = 'flex justify-start gap-2.5 items-start';
        replyEl.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-xs shadow-xs mt-0.5">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="bg-emerald-50/80 text-emerald-900 text-sm px-4 py-3 rounded-2xl rounded-tl-xs max-w-[85%] border border-emerald-200/80 shadow-xs leading-relaxed">
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
