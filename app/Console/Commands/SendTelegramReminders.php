<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class SendTelegramReminders extends Command
{
    protected $signature = 'telegram:send-reminders';

    protected $description = 'Kirim pengingat harian (anggaran, rutin, hutang, future plan) ke pengguna Telegram yang terhubung';

    public function handle(TelegramBotService $botService): int
    {
        $users = User::whereNotNull('telegram_chat_id')
            ->where('telegram_notifications_enabled', true)
            ->get();

        $sentCount = 0;
        foreach ($users as $user) {
            if ($botService->sendDailyRemindersForUser($user)) {
                $sentCount++;
            }
        }

        $this->info("Pengingat Telegram selesai dikirim ke {$sentCount} pengguna.");

        return self::SUCCESS;
    }
}
