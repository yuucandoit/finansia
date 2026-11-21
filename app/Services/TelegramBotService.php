<?php

namespace App\Services;

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function __construct(
        protected SmartRuleService $smartRuleService,
        protected NotificationService $notificationService,
        protected SpendingInsightService $spendingInsightService
    ) {}

    public function sendMessage(string $chatId, string $text): bool
    {
        $token = config('services.telegram.bot_token');
        if (empty($token)) {
            Log::info('Telegram sendMessage skipped (TELEGRAM_BOT_TOKEN not configured)', [
                'chat_id' => $chatId,
            ]);
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Proses payload webhook dari Telegram Bot API.
     */
    public function handleWebhookUpdate(array $update): array
    {
        $message = $update['message'] ?? null;
        if (!$message || empty($message['chat']['id'])) {
            return ['handled' => false, 'reply' => null];
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim((string) ($message['text'] ?? ''));

        if ($text === '') {
            return ['handled' => false, 'reply' => null];
        }

        // 1. Command /start atau /help
        if (preg_match('/^\/(start|help)\b/i', $text)) {
            $reply = "👋 *Selamat datang di Finansia Bot!*\n\n"
                . "Cara menghubungkan akun:\n"
                . "1. Buka menu *Profile* di web Finansia\n"
                . "2. Klik *Buat Kode Tautan Telegram*\n"
                . "3. Kirim perintah: `/link KODE_ANDA`\n\n"
                . "*Perintah tersedia:*\n"
                . "• `/saldo` — Cek total & rincian saldo rekening\n"
                . "• `/ringkasan` — Cek arus kas & batas belanja harian\n"
                . "• `/notifikasi` — Cek tagihan rutin, hutang & anggaran\n"
                . "• *Catat cepat*: cukup ketik misal `Kopi 28rb BCA` atau `Gaji masuk 5jt Mandiri`";

            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'help', 'reply' => $reply];
        }

        // 2. Command /link <CODE>
        if (preg_match('/^\/link\s+([A-Za-z0-9\-]+)/i', $text, $m)) {
            $code = strtoupper(trim($m[1]));
            $user = User::where('telegram_link_code', $code)->first();

            if (!$user) {
                $reply = "❌ Kode tautan `{$code}` tidak ditemukan atau sudah kadaluarsa. Buat kode baru di halaman Profile Finansia.";
                $this->sendMessage($chatId, $reply);
                return ['handled' => true, 'action' => 'link_failed', 'reply' => $reply];
            }

            // Lepas chat_id lama jika pernah terhubung ke akun lain
            User::where('telegram_chat_id', $chatId)
                ->where('id', '!=', $user->id)
                ->update(['telegram_chat_id' => null]);

            $user->update([
                'telegram_chat_id'               => $chatId,
                'telegram_link_code'             => null,
                'telegram_notifications_enabled' => true,
            ]);

            $reply = "✅ Berhasil terhubung dengan akun *{$user->name}*!\n\n"
                . "Sekarang Anda bisa langsung catat transaksi lewat chat (contoh: `Makan siang 35rb`) atau ketik `/saldo` & `/ringkasan`.";

            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'linked', 'user_id' => $user->id, 'reply' => $reply];
        }

        // Cari user yang sudah terhubung
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $reply = "🔒 Akun Telegram ini belum terhubung ke Finansia.\nKirim `/link KODE_ANDA` (ambil kode di halaman *Profile* Finansia).";
            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'unlinked', 'reply' => $reply];
        }

        // 3. Command /saldo
        if (preg_match('/^\/saldo\b/i', $text)) {
            $savings = Saving::where('user_id', $user->id)->orderByDesc('current_balance')->get();
            $total = (float) $savings->sum('current_balance');

            $lines = ["💰 *Total Saldo Anda*: Rp " . number_format($total, 0, ',', '.')];
            foreach ($savings as $s) {
                $lines[] = "• {$s->name}: Rp " . number_format((float) $s->current_balance, 0, ',', '.');
            }
            $reply = implode("\n", $lines);

            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'balance', 'reply' => $reply];
        }

        // 4. Command /ringkasan atau /status
        if (preg_match('/^\/(ringkasan|status)\b/i', $text)) {
            $insights = $this->spendingInsightService->build($user);
            $safe = $insights['safe_to_spend'];
            $heat = $insights['heatmap'];

            $reply = "📊 *Ringkasan Bulan Ini ({$heat['month_label']})*\n"
                . "• Pengeluaran bulan ini: Rp " . number_format($safe['spent_month'], 0, ',', '.') . "\n"
                . "• Belanja hari ini: Rp " . number_format($safe['spent_today'], 0, ',', '.') . "\n"
                . "• Batas aman belanja harian: *Rp " . number_format($safe['daily_safe'], 0, ',', '.') . " / hari* (sisa {$safe['days_remaining']} hari)\n"
                . "• No-Spend Days: {$heat['no_spend_days']} hari";

            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'summary', 'reply' => $reply];
        }

        // 5. Command /notifikasi
        if (preg_match('/^\/notifikasi\b/i', $text)) {
            $items = $this->notificationService->getUserNotifications($user);
            if (empty($items)) {
                $reply = "✅ *Semua aman!* Tidak ada tagihan mendesak atau anggaran yang melewati batas.";
            } else {
                $lines = ["🔔 *Pusat Notifikasi (" . count($items) . ")*"];
                foreach (array_slice($items, 0, 8) as $it) {
                    $lines[] = "{$it['icon']} *{$it['title']}*\n   {$it['message']}";
                }
                $reply = implode("\n", $lines);
            }

            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'notifications', 'reply' => $reply];
        }

        // 6. Quick-Record Transaksi dari pesan bebas
        return $this->handleQuickRecord($user, $chatId, $text);
    }

    /**
     * Parsing pesan teks bebas menjadi transaksi dan simpan langsung ke database.
     */
    protected function handleQuickRecord(User $user, string $chatId, string $text): array
    {
        $parsed = $this->parseQuickMessage($text);
        if (!$parsed || $parsed['amount'] <= 0) {
            $reply = "🤔 Nominal tidak terbaca. Contoh format catat cepat:\n"
                . "• `Kopi Kenangan 28rb BCA`\n"
                . "• `Makan siang 35000`\n"
                . "• `Gaji masuk 5jt Mandiri`";
            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'parse_failed', 'reply' => $reply];
        }

        $savings = Saving::where('user_id', $user->id)->orderBy('id')->get();
        if ($savings->isEmpty()) {
            $reply = "⚠️ Anda belum memiliki rekening tabungan di Finansia. Tambahkan rekening dulu di web.";
            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'no_saving', 'reply' => $reply];
        }

        // Cocokkan nama rekening dari teks pesan (jika disebutkan)
        $textLower = strtolower($text);
        $selectedSaving = $savings->first(function (Saving $s) use ($textLower) {
            $nameLower = strtolower(trim($s->name));
            return $nameLower !== '' && str_contains($textLower, $nameLower);
        }) ?? $savings->first();

        $amount = $parsed['amount'];
        $type = $parsed['type'];
        $description = $parsed['description'] !== '' ? $parsed['description'] : 'Transaksi Telegram';

        $suggestion = $this->smartRuleService->suggestForUser($user->id, $description, now()->toDateString());
        $categoryId = $suggestion['category_id'] ?? null;
        $categoryName = $suggestion['category_name'] ?? 'Lainnya';
        $budgetItemId = $suggestion['budget_item_id'] ?? null;

        try {
            $tx = DB::transaction(function () use ($user, $selectedSaving, $type, $amount, $description, $categoryId, $budgetItemId) {
                $saving = Saving::where('user_id', $user->id)->lockForUpdate()->findOrFail($selectedSaving->id);

                if ($type === 'Cash Out' && (float) $saving->current_balance < $amount) {
                    throw new \Exception("Saldo rekening *{$saving->name}* tidak mencukupi (Sisa: Rp " . number_format((float) $saving->current_balance, 0, ',', '.') . ").");
                }

                $created = Transaction::create([
                    'user_id'          => $user->id,
                    'savings_id'       => $saving->id,
                    'category_id'      => $categoryId,
                    'budget_item_id'   => $budgetItemId,
                    'type'             => $type,
                    'direction'        => $type === 'Cash In' ? Transaction::DIRECTION_IN : Transaction::DIRECTION_OUT,
                    'description'      => $description,
                    'amount'           => $amount,
                    'transaction_date' => now()->toDateString(),
                    'is_manual'        => true,
                ]);

                if ($type === 'Cash In') {
                    $saving->increment('current_balance', $amount);
                } else {
                    $saving->decrement('current_balance', $amount);
                }

                return $created;
            });

            $freshSaving = $selectedSaving->fresh();
            $icon = $type === 'Cash In' ? '💵' : '💸';
            $reply = "{$icon} *Transaksi Tercatat!*\n"
                . "• Deskripsi: {$tx->description}\n"
                . "• Nominal: *Rp " . number_format($amount, 0, ',', '.') . "* ({$type})\n"
                . "• Kategori: {$categoryName}\n"
                . "• Rekening: {$freshSaving->name} (Sisa: Rp " . number_format((float) $freshSaving->current_balance, 0, ',', '.') . ")";

            $this->sendMessage($chatId, $reply);
            return [
                'handled'        => true,
                'action'         => 'recorded',
                'transaction_id' => $tx->id,
                'reply'          => $reply,
            ];
        } catch (\Throwable $e) {
            $reply = "❌ Gagal mencatat transaksi: " . $e->getMessage();
            $this->sendMessage($chatId, $reply);
            return ['handled' => true, 'action' => 'record_failed', 'reply' => $reply];
        }
    }

    /**
     * Ekstrak nominal, tipe (Cash In / Cash Out), dan deskripsi dari kalimat singkat.
     */
    public function parseQuickMessage(string $text): ?array
    {
        $clean = trim($text);
        if (!preg_match('/(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(jt|juta|rb|ribu|k)?\b/i', $clean, $m)) {
            return null;
        }

        $numStr = $m[1];
        $unit = strtolower($m[2] ?? '');

        if ($unit === 'jt' || $unit === 'juta') {
            $amount = (float) str_replace(',', '.', $numStr) * 1_000_000;
        } elseif ($unit === 'rb' || $unit === 'ribu' || $unit === 'k') {
            $amount = (float) str_replace(',', '.', $numStr) * 1_000;
        } else {
            // Hapus pemisah ribuan titik/koma (misal 35.000 -> 35000)
            $amount = (float) preg_replace('/[.,](?=\d{3}\b)/', '', $numStr);
        }

        $amount = round($amount, 0);
        if ($amount <= 0) {
            return null;
        }

        $lower = strtolower($clean);
        $isIncome = (bool) preg_match('/\b(masuk|gaji|terima|bonus|thr|pendapatan|income|freelance)\b/i', $lower);
        $type = $isIncome ? 'Cash In' : 'Cash Out';

        $desc = trim(preg_replace('/\s+/', ' ', str_replace($m[0], '', $clean)));

        return [
            'amount'      => $amount,
            'type'        => $type,
            'description' => $desc,
        ];
    }

    /**
     * Kirim ringkasan pengingat harian ke user yang menautkan Telegram.
     */
    public function sendDailyRemindersForUser(User $user): bool
    {
        if (empty($user->telegram_chat_id) || !$user->telegram_notifications_enabled) {
            return false;
        }

        $items = $this->notificationService->getUserNotifications($user);
        if (empty($items)) {
            return false;
        }

        $lines = ["☀️ *Pengingat Harian Finansia ({$user->name})*"];
        foreach (array_slice($items, 0, 8) as $it) {
            $lines[] = "{$it['icon']} *{$it['title']}*\n   {$it['message']}";
        }
        $lines[] = "\nKetik `/ringkasan` untuk cek batas aman belanja hari ini.";

        return $this->sendMessage($user->telegram_chat_id, implode("\n", $lines));
    }
}
