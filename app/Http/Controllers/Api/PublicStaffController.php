<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;

class PublicStaffController extends Controller
{
    public function index()
    {
        return Staff::query()
            ->select([
                'id','name','initials','slug','logo','motto','description',
                'leader_name','leader_rank','leader_photo','order',
                'contact_email','contact_phone','contact_hotline','contact_address',
                'contact_hours','contact_map_url','contact_socials',
            ])
            ->orderBy('order')
            ->get();
    }

    public function show(string $slug)
    {
        return Staff::query()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}