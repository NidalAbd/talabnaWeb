<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string|max:2000',
        ]);

        try {
            Mail::raw(
                "Name: {$request->name}\nEmail: {$request->email}\n\nMessage:\n{$request->message}",
                function ($mail) use ($request) {
                    $mail->to(config('mail.from.address', 'support@talbna.cloud'))
                        ->replyTo($request->email, $request->name)
                        ->subject("Contact Form: {$request->name}");
                }
            );

            return response()->json(['success' => true, 'message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }
}
