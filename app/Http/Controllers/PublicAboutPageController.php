<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Support\Facades\Cache;

class PublicAboutPageController extends Controller
{
    public function __invoke()
    {
        $staffs = Cache::remember('seo:about:staffs:v1', 300, function () {
            return Staff::query()
                ->select([
                    'id',
                    'parent_staff_id',
                    'name',
                    'initials',
                    'slug',
                    'order',
                ])
                ->whereNotNull('slug')
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        });

        $description = 'Présentation officielle des Forces Armées Maliennes : missions, organisation institutionnelle, ministère, état-major général, directions, services et structures de défense nationale.';

        $seo = [
            'title' => 'À propos | Forces Armées Maliennes',
            'description' => $description,
            'canonical' => url('/about'),
            'type' => 'website',
            'image' => url('/images/og-default.jpg'),
        ];

        $seoAbout = [
            'staffs' => $staffs,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => 'Forces Armées Maliennes',
            'alternateName' => 'FAMa',
            'url' => url('/about'),
            'description' => $description,
        ];

        return view('front', compact('seo', 'seoAbout', 'jsonLd'));
    }
}
