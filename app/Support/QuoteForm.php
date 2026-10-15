<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Which optional fields the public quote form shows, and which of them are required.
 * Edited in the admin panel: Settings → Quote form.
 * Name, phone, transport mode, origin and destination are always shown and required.
 */
class QuoteForm
{
    /** field key => [label in the panel, shown by default, required by default] */
    public const OPTIONAL_FIELDS = [
        'company' => ['نام شرکت', true, false],
        'email' => ['ایمیل', true, false],
        'cargo_type' => ['نوع کالا', true, true],
        'weight_kg' => ['وزن (کیلوگرم)', true, false],
        'volume_cbm' => ['حجم (متر مکعب)', true, false],
        'load_type' => ['نوع بار (کانتینر، دربست، گروپاژ)', true, false],
        'services' => ['خدمات جانبی (ترخیص و بیمه)', true, false],
        'description' => ['توضیحات', true, false],
        'attachment' => ['پیوست فایل (پکینگ لیست، پیش‌فاکتور)', true, false],
    ];

    /** @return array<string, array{enabled: bool, required: bool}> */
    public static function config(): array
    {
        $saved = Setting::get('quote_form', []);
        $config = [];

        foreach (self::OPTIONAL_FIELDS as $key => [, $enabled, $required]) {
            $isEnabled = (bool) ($saved[$key]['enabled'] ?? $enabled);
            $config[$key] = [
                'enabled' => $isEnabled,
                // A hidden field can never be required.
                'required' => $isEnabled && (bool) ($saved[$key]['required'] ?? $required),
            ];
        }

        return $config;
    }
}
