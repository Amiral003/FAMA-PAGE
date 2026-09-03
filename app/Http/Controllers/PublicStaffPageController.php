<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicStaffPageController extends Controller
{
    public function __invoke(string $slug)
    {
        $staff = Cache::remember(
            'seo:staff:' . $slug . ':v1',
            300,
            function () use ($slug) {
                return Staff::query()
                    ->select([
                        'id',
                        'parent_staff_id',
                        'name',
                        'initials',
                        'slug',
                        'logo',
                        'motto',
                        'description',
                        'missions',
                        'leader_name',
                        'leader_rank',
                        'leader_photo',
                        'leader_photos',
                        'leader_word',
                        'second_leader_name',
                        'second_leader_rank',
                        'second_leader_photo',
                        'second_leader_word',
                        'contact_email',
                        'contact_phone',
                        'contact_hotline',
                        'contact_address',
                        'contact_hours',
                        'contact_map_url',
                    ])
                    ->where('slug', $slug)
                    ->firstOrFail();
            }
        );

        $staff->description = SafeHtml::clean($staff->description);
        $staff->missions = SafeHtml::clean($staff->missions);

        $descriptionSource =
            strip_tags($staff->description ?? '')
            ?: strip_tags($staff->missions ?? '')
            ?: 'Présentation officielle de ' . trim($staff->name) . '.';

        $description = Str::limit(
            preg_replace('/\s+/u', ' ', trim(html_entity_decode($descriptionSource))),
            160,
            '...'
        );

        $image = $staff->logo
            ? url('/storage/' . ltrim($staff->logo, '/'))
            : url('/images/og-default.jpg');

        $seo = [
            'title' => trim($staff->name) . ' | Forces Armées Maliennes',
            'description' => $description,
            'canonical' => url('/etat-major/' . $staff->slug),
            'type' => 'website',
            'image' => $image,
        ];

        $seoStaff = [
            'staff' => $staff,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => trim($staff->name),
            'alternateName' => $staff->initials ?: null,
            'url' => url('/etat-major/' . $staff->slug),
            'description' => $description,
            'parentOrganization' => [
                '@type' => 'GovernmentOrganization',
                'name' => 'Forces Armées Maliennes',
                'url' => url('/'),
            ],
        ];

        return view('front', compact('seo', 'seoStaff', 'jsonLd'));
    }
}
