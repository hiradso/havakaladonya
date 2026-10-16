<?php

namespace App\Support;

/**
 * Service page slugs. The page content lives in resources/js/content/services.ts;
 * keep both lists in the same order.
 */
class Services
{
    public const SLUGS = [
        'air-freight',
        'sea-freight',
        'road-freight',
        'rail-freight',
        'customs-clearance',
        'sourcing',
        'passenger-freight',
        'consulting',
        'trading-company',
    ];

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
