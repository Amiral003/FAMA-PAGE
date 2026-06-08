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
                'id','parent_staff_id','name','initials','slug','logo','motto','description',
                'leader_name','leader_rank','leader_photo','order',
                'contact_email','contact_phone','contact_hotline','contact_address',
                'contact_hours','contact_map_url','contact_socials',
                'second_leader_rank','second_leader_name','second_leader_photo','second_leader_word'
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
