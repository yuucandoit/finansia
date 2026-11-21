<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function webhook(Request $request, TelegramBotService $botService): JsonResponse
    {
        $expectedSecret = config('services.telegram.webhook_secret');
        if (!empty($expectedSecret)) {
            $provided = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
            if (!hash_equals($expectedSecret, $provided)) {
                return response()->json(['status' => 'forbidden'], 403);
            }
        }

        $result = $botService->handleWebhookUpdate($request->all());

        return response()->json([
            'ok'     => true,
            'result' => $result,
        ]);
    }

    public function generateLinkCode(Request $request): RedirectResponse
    {
        $user = $request->user();

        do {
            $code = 'FIN-' . strtoupper(Str::random(6));
        } while (\App\Models\User::where('telegram_link_code', $code)->exists());

        $user->update(['telegram_link_code' => $code]);

        return redirect()->route('profile.edit')
            ->with('telegram_status', "Kode tautan baru dibuat: {$code}. Kirim /link {$code} ke bot Telegram Finansia.");
    }

    public function toggleNotifications(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'telegram_notifications_enabled' => !$user->telegram_notifications_enabled,
        ]);

        $msg = $user->telegram_notifications_enabled
            ? 'Pengingat harian Telegram diaktifkan.'
            : 'Pengingat harian Telegram dijeda.';

        return redirect()->route('profile.edit')->with('telegram_status', $msg);
    }

    public function unlink(Request $request): RedirectResponse
    {
        $request->user()->update([
            'telegram_chat_id'   => null,
            'telegram_link_code' => null,
        ]);

        return redirect()->route('profile.edit')->with('telegram_status', 'Koneksi Telegram berhasil diputus.');
    }
}
