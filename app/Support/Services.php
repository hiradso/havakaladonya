<?php

namespace App\Support;

/**
 * Old WordPress addresses of the service pages. The services themselves are App\Models\Service
 * (edited in the panel: Content → Services).
 */
class Services
{
    /**
     * Addresses of the old WordPress site → new pages, so links on Google and
     * elsewhere keep working (301 = permanent, search engines move the ranking over).
     */
    public const LEGACY_REDIRECTS = [
        'services' => '/services',
        'about' => '/about',
        'contact' => '/contact',
        'blog-classic-3-columns' => '/blog',
        'حمل-و-نقل-هوایی' => '/services/air-freight',
        'حمل-و-نقل-دریایی' => '/services/sea-freight',
        'حمل-و-نقل-جاده-ای' => '/services/road-freight',
        'حمل-و-نقل-ریلی' => '/services/rail-freight',
        'فرید-بار-مسافری' => '/services/passenger-freight',
        'فریت-بار-مسافری' => '/services/passenger-freight',
        'ترخیص-کالا' => '/services/customs-clearance',
        'تامین-کالا' => '/services/sourcing',
        'صادرات-واردات' => '/services/sourcing',
        'خرید-بازرگانی' => '/services/trading-company',
        'مشاوره-بازرگانی' => '/services/consulting',
        'مشاوره-حمل-و-نقلی' => '/services/consulting',
    ];
}
