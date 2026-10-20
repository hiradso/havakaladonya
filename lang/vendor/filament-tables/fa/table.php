<?php

// Persian for strings missing from Filament's own fa translation (merged with it).
return [
    'column_manager' => [
        'position' => 'جایگاه :position از :count.',
        'position_in_group' => 'جایگاه :position از :count در :group.',
        'reordered' => ':column به جایگاه :position از :count منتقل شد.',
        'reordered_deferred' => ':column به جایگاه :position از :count منتقل شد. برای ذخیره، ستون‌ها را اعمال کنید.',
        'reordered_in_group' => ':column به جایگاه :position از :count در :group منتقل شد.',
        'reordered_in_group_deferred' => ':column به جایگاه :position از :count در :group منتقل شد. برای ذخیره، ستون‌ها را اعمال کنید.',
        'actions' => [
            'move_up' => ['label' => 'انتقال :column به بالا'],
            'move_down' => ['label' => 'انتقال :column به پایین'],
            'reorder' => ['label' => 'جابه‌جایی ستون'],
        ],
    ],
    'columns' => [
        'editable' => [
            'saving' => 'در حال ذخیره…',
            'saved' => 'ذخیره شد.',
            'failure' => 'ذخیره تأیید نشد. صفحه را تازه کنید و دوباره تلاش کنید.',
            'unavailable' => 'این مورد دیگر قابل ویرایش نیست.',
        ],
        'icon' => ['boolean' => ['true' => 'بله', 'false' => 'خیر']],
    ],
    'actions' => [
        'reorder_record' => ['label' => 'جابه‌جایی مورد :key'],
        'move_record_up' => ['label' => 'انتقال :label به بالا'],
        'move_record_down' => ['label' => 'انتقال :label به پایین'],
        'toggle_record_content' => ['label' => 'باز/بسته کردن مورد :key'],
    ],
    'loading' => 'در حال بارگذاری…',
    'reordering' => [
        'position' => ':label، جایگاه :position از :count.',
        'moved' => ':label به جایگاه :position از :count منتقل شد.',
    ],
    'result_count' => '{0} نتیجه‌ای نیست|[1,*] :count نتیجه',
];
