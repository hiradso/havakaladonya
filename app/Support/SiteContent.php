<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Setting;

/**
 * Website texts, pictures and contact details that the general manager edits in the panel
 * (admin → Site content). Each section is one Setting row "content_{section}":
 *
 *   ['fa' => ['home' => [...texts]], 'en' => [...], 'hero_media' => 12, 'show' => [...], ...]
 *
 * The Persian/English texts use the same keys as resources/js/i18n/{fa,en}.ts, so the website
 * simply lays them over its built-in dictionary. Defaults (= the original site) live in
 * resources/content/defaults.json; nothing changes on the site until someone saves an edit.
 */
class SiteContent
{
    public const SECTIONS = ['home', 'about', 'contact', 'footer'];

    /** Built-in pictures used until a picture is chosen from the media library. */
    private const STATIC_IMAGES = [
        'home_hero' => ['/images/photos/port-plane-800.webp', '/images/photos/port-plane-1600.webp', 1600, 900],
        'about_header' => ['/images/photos/containers-yard-800.webp', '/images/photos/containers-yard-1600.webp', 1600, 900],
    ];

    private static ?array $defaults = null;

    /** @return array<string, mixed> */
    public static function defaults(string $section): array
    {
        self::$defaults ??= json_decode((string) file_get_contents(resource_path('content/defaults.json')), true) ?: [];

        return self::$defaults[$section] ?? [];
    }

    /** Saved values laid over the defaults. @return array<string, mixed> */
    public static function get(string $section): array
    {
        $saved = Setting::get('content_'.$section, []);

        return self::merge(self::defaults($section), is_array($saved) ? $saved : []);
    }

    public static function save(string $section, array $data): void
    {
        Setting::set('content_'.$section, $data);
    }

    public static function reset(string $section): void
    {
        Setting::query()->where('key', 'content_'.$section)->delete();
        Setting::set('content_'.$section, self::defaults($section));
    }

    /**
     * Everything the public website needs, for one language (shared with every page).
     *
     * @return array<string, mixed>
     */
    public static function forFrontend(string $locale): array
    {
        $locale = $locale === 'en' ? 'en' : 'fa';
        $home = self::get('home');
        $about = self::get('about');
        $contact = self::get('contact');
        $footer = self::get('footer');

        $texts = array_replace_recursive(
            $home[$locale] ?? [],
            $about[$locale] ?? [],
            $contact[$locale] ?? [],
            $footer[$locale] ?? [],
        );
        // Lists must replace, not merge item by item.
        foreach ([['home', 'stats', $home], ['about', 'story', $about], ['about', 'timeline', $about], ['about', 'values', $about]] as [$ns, $key, $source]) {
            $texts[$ns][$key] = array_values((array) ($source[$locale][$ns][$key] ?? []));
        }

        $ids = array_filter([$home['hero_media'] ?? null, $about['header_media'] ?? null, ...array_column($about['team'] ?? [], 'photo_media')]);
        $media = $ids ? Media::query()->whereIn('id', $ids)->get()->keyBy('id') : collect();
        $image = function (?int $id, string $static, string $alt) use ($media, $locale): array {
            if ($id && ($m = $media->get($id))) {
                return ['alt' => $alt ?: $m->alt($locale)] + $m->toImage($locale);
            }
            [$small, $large, $w, $h] = self::STATIC_IMAGES[$static];

            return ['src' => $large, 'srcset' => "{$small} 800w, {$large} 1600w", 'width' => $w, 'height' => $h, 'alt' => $alt];
        };

        return [
            'texts' => $texts,
            'contact' => self::contactLinks($contact),
            'images' => [
                'homeHero' => $image($home['hero_media'] ?? null, 'home_hero', (string) ($texts['home']['photoAlt'] ?? '')),
                'aboutHeader' => $image($about['header_media'] ?? null, 'about_header', ''),
            ],
            'team' => array_values(array_filter(array_map(function (array $member) use ($media, $locale): ?array {
                $name = trim((string) ($member['name_'.$locale] ?? '')) ?: trim((string) ($member['name_fa'] ?? ''));
                if ($name === '') {
                    return null;
                }
                $photo = null;
                if (($id = $member['photo_media'] ?? null) && ($m = $media->get($id))) {
                    $photo = $m->toImage($locale);
                } elseif (filled($member['photo_default'] ?? null)) {
                    $photo = ['src' => '/images/photos/'.$member['photo_default'].'.webp', 'srcset' => '', 'width' => 640, 'height' => 800];
                }

                return [
                    'name' => $name,
                    'role' => trim((string) ($member['role_'.$locale] ?? '')),
                    'photo' => $photo ? ['alt' => $name] + $photo : null,
                ];
            }, (array) ($about['team'] ?? [])))),
            'show' => [
                'home' => array_map('boolval', (array) ($home['show'] ?? [])),
                'about' => array_map('boolval', (array) ($about['show'] ?? [])),
            ],
        ];
    }

    /** Phone numbers as typed in the panel → display text + tel:/WhatsApp links. */
    public static function contactLinks(array $c): array
    {
        $display = fn (?string $v) => trim((string) $v);

        return [
            'phone' => ['display' => $display($c['phone'] ?? ''), 'href' => self::telHref($c['phone'] ?? '')],
            'mobile' => ['display' => $display($c['mobile'] ?? ''), 'href' => self::telHref($c['mobile'] ?? '')],
            'fax' => ['display' => $display($c['fax'] ?? '')],
            'whatsapp' => ($wa = self::international($c['whatsapp'] ?? '')) ? 'https://wa.me/'.ltrim($wa, '+') : '',
            'email' => $display($c['email'] ?? ''),
            'instagram' => ($ig = ltrim($display($c['instagram'] ?? ''), '@'))
                ? ['handle' => $ig, 'href' => 'https://instagram.com/'.$ig] : null,
            'telegram' => ($tg = ltrim($display($c['telegram'] ?? ''), '@'))
                ? ['handle' => $tg, 'href' => 'https://t.me/'.$tg] : null,
            'linkedin' => $display($c['linkedin'] ?? '') ?: null,
            'mapUrl' => $display($c['map_url'] ?? ''),
            // For structured data (Google): +98-21-88015232
            'phoneIntl' => self::international($c['phone'] ?? ''),
            'mobileIntl' => self::international($c['mobile'] ?? ''),
            'faxIntl' => self::international($c['fax'] ?? ''),
        ];
    }

    public static function telHref(?string $number): string
    {
        $intl = self::international($number);

        return $intl ? 'tel:'.$intl : '';
    }

    /** "021 8801 5232" / "۰۹۱۲…" / "+98 21…" → "+982188015232" (Iran numbers starting with 0 get +98). */
    public static function international(?string $number): string
    {
        $n = Digits::toLatin((string) $number);
        $plus = str_starts_with(trim($n), '+') || str_starts_with(trim($n), '00');
        $digits = preg_replace('/\D+/', '', $n) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with(trim($n), '00')) {
            $digits = substr($digits, 2);
        } elseif (! $plus && str_starts_with($digits, '0')) {
            $digits = '98'.substr($digits, 1);
        }

        return '+'.$digits;
    }

    /** Associative arrays merge key by key; lists and plain values from $saved replace the default. */
    private static function merge(array $default, array $saved): array
    {
        foreach ($saved as $key => $value) {
            if (is_array($value) && ! array_is_list($value) && is_array($default[$key] ?? null) && ! array_is_list($default[$key])) {
                $default[$key] = self::merge($default[$key], $value);
            } elseif ($value !== null) {
                $default[$key] = $value;
            }
        }

        return $default;
    }
}
