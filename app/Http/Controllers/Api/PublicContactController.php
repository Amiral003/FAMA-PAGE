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
            Log::warning('Contact honeypot triggered', [
                'ip_hash' => $this->hashIp($request->ip()),
                'route' => $request->path(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw ValidationException::withMessages([
                'website' => ['Champ invalide.'],
            ]);
        }

        // Accepter indifferemment telephone, phone ou tel.
        $telephoneValue = $request->input('telephone') ?? $request->input('phone') ?? $request->input('tel') ?? null;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        if (empty($telephoneValue)) {
            throw ValidationException::withMessages([
                'telephone' => ['Le numero de telephone est requis.'],
            ]);
        }

        $telephoneClean = preg_replace('/[^0-9+]/', '', trim($telephoneValue));
        $email = filled($data['email'] ?? null)
            ? mb_strtolower(trim($data['email']))
            : null;

        $msg = ContactMessage::create([
            'name' => trim($data['name']),
            'email' => $email,
            'telephone' => $telephoneClean,
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
        ]);

        Log::info('Contact message received', [
            'contact_message_id' => $msg->id,
            'has_email' => $email !== null,
            'route' => $request->path(),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $msg->id,
        ], 201);
    }

    private function hashIp(?string $ip): string
    {
        return hash('sha256', ($ip ?? 'unknown') . '|' . config('app.key'));
    }
}
