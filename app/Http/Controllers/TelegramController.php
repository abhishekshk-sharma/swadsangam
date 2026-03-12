<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        Log::info('Telegram webhook received', $request->all());
        
        $telegram = new TelegramService();
        $telegram->handleWebhook($request->all());
        
        return response()->json(['ok' => true]);
    }
}
