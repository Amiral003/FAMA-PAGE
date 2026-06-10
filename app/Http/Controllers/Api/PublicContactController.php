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
        // Log pour voir ce qui arrive
        Log::info('Contact form data:', $request->all());

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

        // Accepter indifféremment telephone, phone ou tel
        $telephoneValue = $request->input('telephone') ?? $request->input('phone') ?? $request->input('tel') ?? null;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],             'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        // Validation manuelle du téléphone
        if (empty($telephoneValue)) {
            throw ValidationException::withMessages([
                'telephone' => ['Le numéro de téléphone est requis.'],
            ]);
        }

        // Nettoyer le numéro de téléphone
        $telephoneClean = preg_replace('/[^0-9+]/', '', trim($telephoneValue));

        $msg = ContactMessage::create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'telephone' => $telephoneClean,
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
