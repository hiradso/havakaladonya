<?php

namespace App\Support;

/** Gender of a staff user; only used to pick the panel avatar. */
class Gender
{
    public const MALE = 'male';

    public const FEMALE = 'female';

    public const LABELS = [
        self::MALE => 'آقا',
        self::FEMALE => 'خانم',
    ];
}
