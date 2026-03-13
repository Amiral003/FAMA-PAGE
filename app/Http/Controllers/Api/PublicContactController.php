<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Staff;
use Illuminate\Http\Request;

class PublicContactController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'staff_slug' => ['nullable','string','max:255'],
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255'],
            'subject'    => ['required','string','max:255'],
            'message'    => ['required','string','min:10','max:5000'],
            'website' => ['nullable', 'size:0'],
            // honeypot optionnel : 'website' => ['nullable','size:0']
        ]);

        $staff = null;
        if (!empty($data['staff_slug'])) {
            $staff = Staff::where('slug', $data['staff_slug'])->first();
        }

        $msg = ContactMessage::create([
            'staff_id'   => $staff?->id,
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
        ]);

        // OPTION EMAIL (tu peux activer après)
        // $to = $staff?->contact_email ?? config('mail.contact_default_to');
        // if ($to) Mail::to($to)->send(new ContactMessageMail($msg));

        return response()->json([
            'ok' => true,
            'id' => $msg->id,
        ]);
    }
}