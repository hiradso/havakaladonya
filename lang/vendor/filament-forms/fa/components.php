<?php

// Persian for strings missing from Filament's own fa translation (merged with it).
return [
    'checkbox_list' => ['required_description' => 'دست‌کم یک گزینه را انتخاب کنید.'],
    'color_picker' => ['panel_label' => 'انتخاب رنگ'],
    'date_time_picker' => [
        'month_select' => ['label' => 'ماه'],
        'year_input' => ['label' => 'سال'],
        'hour_input' => ['label' => 'ساعت'],
        'minute_input' => ['label' => 'دقیقه'],
        'second_input' => ['label' => 'ثانیه'],
    ],
    'file_upload' => [
        'placeholder' => 'فایلی بارگذاری نشده است',
        'actions' => [
            'download' => ['label' => 'دانلود'],
            'open' => ['label' => 'باز کردن در زبانه‌ی جدید'],
        ],
        'editor' => ['label' => 'ویرایشگر تصویر'],
    ],
    'repeater' => [
        'reordering' => [
            'item' => 'مورد',
            'position' => ':label، جایگاه :position از :count',
            'moved' => ':label به جایگاه :position از :count منتقل شد',
        ],
        'columns' => ['actions' => ['label' => 'عملیات'], 'reorder' => ['label' => 'جابه‌جایی']],
    ],
    'rich_editor' => [
        'actions' => ['close_panel' => ['label' => 'بستن']],
        'toolbar' => ['label' => 'نوار ابزار ویرایشگر'],
        'tools' => ['h4' => 'تیتر ۴', 'h5' => 'تیتر ۵', 'h6' => 'تیتر ۶'],
    ],
    'select' => [
        'actions' => [
            'clear' => ['label' => 'پاک کردن انتخاب'],
            'remove_option' => ['label' => 'حذف :label'],
        ],
        'error_message' => 'گزینه‌ها بارگذاری نشدند. دوباره تلاش کنید.',
        'required_description' => 'یک گزینه را انتخاب کنید.',
        'search_label' => 'جستجو',
    ],
    'tags_input' => ['tag_added' => 'اضافه شد: :tag', 'tag_removed' => 'حذف شد: :tag'],
    'toggle_buttons' => ['required_description' => 'دست‌کم یک گزینه را انتخاب کنید.'],
];
