<?php

namespace App\Services;

use App\Models\{User, TelegramRequest};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $enabled;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->enabled = env('TELEGRAM_ENABLED', false);
    }

    public function handleWebhook($data)
    {
        if (!isset($data['message'])) {
            return;
        }

        $message = $data['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $from = $message['from'];

        if ($text === '/start') {
            $this->handleStartCommand($chatId, $from);
        }
    }

    protected function handleStartCommand($chatId, $from)
    {
        $username = $from['username'] ?? null;
        $firstName = $from['first_name'] ?? null;
        $lastName = $from['last_name'] ?? null;
        $phone = $from['phone_number'] ?? null;

        // Check if user exists with this phone
        $user = null;
        if ($phone) {
            $user = User::where('phone', $phone)->first();
        }

        if ($user) {
            // Update user with telegram details
            $user->update([
                'telegram_chat_id' => $chatId,
                'telegram_username' => $username
            ]);

            $this->sendMessage($chatId, "✅ Successfully linked to your account!\n\nYou will now receive order notifications.");
        } else {
            // Store in telegram_requests for admin approval
            TelegramRequest::updateOrCreate(
                ['chat_id' => $chatId],
                [
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'status' => 'pending'
                ]
            );

            $this->sendMessage($chatId, "👋 Welcome!\n\nYour request has been sent to admin for approval.\n\nChat ID: {$chatId}\nUsername: @{$username}");
        }
    }

    public function sendOrderNotification($chatId, $orderData)
    {
        if (!$this->enabled || !$chatId) {
            return false;
        }

        $message = "🔔 *New Order Alert!*\n\n";
        $message .= "📋 Order #" . $orderData['order_id'] . "\n";
        $message .= "🪑 Table: " . $orderData['table_name'] . "\n";
        $message .= "⏰ Time: " . $orderData['time'] . "\n\n";
        $message .= "*Items:*\n";
        
        foreach ($orderData['items'] as $item) {
            $message .= "• {$item['quantity']}x {$item['name']}\n";
        }
        
        $message .= "\n💰 Total: ₹" . number_format($orderData['total'], 2);

        return $this->sendMessage($chatId, $message);
    }

    public function sendMessage($chatId, $text)
    {
        if (!$this->enabled) {
            Log::info('Telegram disabled', ['chat_id' => $chatId]);
            return false;
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'Markdown'
                ]);

            Log::info('Telegram message sent', ['chat_id' => $chatId, 'status' => $response->status()]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
