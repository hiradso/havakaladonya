<?php

namespace App\Support;

/**
 * Staff roles in the admin panel.
 *
 * | Section                        | admin | sales_ops | finance | writer |
 * |--------------------------------|:-----:|:---------:|:-------:|:------:|
 * | Quote requests                 | edit  | edit      | view    | –      |
 * | Shipments & tracking events    | edit  | edit      | view    | –      |
 * | Contact messages, live chat    | ✓     | ✓         | –       | –      |
 * | Blog articles                  | ✓     | –         | –       | ✓      |
 * | Tracking stages, settings      | ✓     | –         | –       | –      |
 * | Staff users                    | ✓     | –         | –       | –      |
 */
class Roles
{
    public const ADMIN = 'admin';

    public const SALES_OPS = 'sales_ops';

    public const FINANCE = 'finance';

    public const WRITER = 'writer';

    public const LABELS = [
        self::ADMIN => 'مدیر کل',
        self::SALES_OPS => 'کارشناس فروش و عملیات',
        self::FINANCE => 'مالی',
        self::WRITER => 'نویسنده',
    ];

    /** Who handles customers: gets new-request emails, sees the live chat. */
    public const CUSTOMER_TEAM = [self::ADMIN, self::SALES_OPS];
}
