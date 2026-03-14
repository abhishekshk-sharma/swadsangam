<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TelegramRequest, Employee};
use Illuminate\Http\Request;

class TelegramIntegrationController extends Controller
{
    public function index()
    {
        $requests    = TelegramRequest::orderBy('created_at', 'desc')->get();
        $linkedUsers = Employee::whereNotNull('telegram_chat_id')->get();

        return view('admin.telegram.index', compact('requests', 'linkedUsers'));
    }

    public function linkUser(Request $request, $requestId)
    {
        $telegramRequest = TelegramRequest::findOrFail($requestId);

        $request->validate([
            'user_id' => 'required|exists:employees,id',
        ]);

        $user = Employee::findOrFail($request->user_id);
        $user->update([
            'telegram_chat_id'  => $telegramRequest->chat_id,
            'telegram_username' => $telegramRequest->username,
        ]);

        $telegramRequest->update(['status' => 'approved']);

        return back()->with('success', 'User linked successfully!');
    }

    public function reject($requestId)
    {
        TelegramRequest::findOrFail($requestId)->update(['status' => 'rejected']);
        return back()->with('success', 'Request rejected');
    }

    public function unlink($userId)
    {
        Employee::findOrFail($userId)->update([
            'telegram_chat_id'  => null,
            'telegram_username' => null,
        ]);

        return back()->with('success', 'User unlinked from Telegram');
    }
}
