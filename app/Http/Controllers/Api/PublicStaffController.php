<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\Cache;

class PublicStaffController extends Controller
{
public function index()
{
    return Cache::remember('public:staffs:index:v1', 300, function () {
        return Staff::query()
            ->select([
                'id','parent_staff_id','name','initials','slug','logo','motto','description',
                'leader_name','leader_rank','leader_photo','leader_photos','order',
                'contact_email','contact_phone','contact_hotline','contact_address',
                'contact_hours','contact_map_url','contact_socials',
                'second_leader_rank','second_leader_name','second_leader_photo','second_leader_word'
            ])
            ->orderBy('order')
            ->get()
            ->each(fn (Staff $staff) => $this->sanitizeStaffHtml($staff));
    });
}

    public function show(string $slug)
    {
        $staff = Staff::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->sanitizeStaffHtml($staff);

        return $staff;
    }

    private function sanitizeStaffHtml(Staff $staff): void
    {
        $staff->description = SafeHtml::clean($staff->description);
        $staff->missions = SafeHtml::clean($staff->missions);
    }
}
