<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PublicContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! empty($request->input('website'))) {
            Log::warning('Honeypot contact déclenché', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->path(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw ValidationException::withMessages([
                'website' => ['Champ invalide.'],
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $msg = ContactMessage::create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $msg->id,
        ], 201);
    }
}