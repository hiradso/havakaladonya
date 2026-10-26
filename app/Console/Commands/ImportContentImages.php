<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use App\Support\SiteContent;
use Illuminate\Console\Command;

/**
 * One-time: copies the site's built-in photos (home banner, about header, team) into the
 * media library and links them in "Site content", so they can be seen and swapped in the panel.
 * Safe to run again: anything already linked is left alone.
 */
class ImportContentImages extends Command
{
    protected $signature = 'content:import-images';

    protected $description = 'Add the built-in site photos to the media library and link them in Site content';

    public function handle(): int
    {
        $home = SiteContent::get('home');
        $about = SiteContent::get('about');

        if (empty($home['hero_media'])) {
            $home['hero_media'] = $this->import('port-plane-1600', $home['fa']['home']['photoAlt'] ?? null, $home['en']['home']['photoAlt'] ?? null, 'بنر صفحه اصلی');
            SiteContent::save('home', $home);
        }

        if (empty($about['header_media'])) {
            $about['header_media'] = $this->import('containers-yard-1600', 'حیاط کانتینرها در بندر', 'Container yard at the port', 'عکس درباره ما');
        }
        foreach ($about['team'] as $i => $member) {
            if (empty($member['photo_media']) && filled($member['photo_default'] ?? null)) {
                $about['team'][$i]['photo_media'] = $this->import($member['photo_default'], $member['name_fa'] ?? null, $member['name_en'] ?? null, $member['name_fa'] ?? null);
            }
        }
        SiteContent::save('about', $about);

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function import(string $file, ?string $altFa, ?string $altEn, ?string $title): ?int
    {
        $path = public_path("images/photos/{$file}.webp");
        if (! is_file($path)) {
            $this->warn("Missing: {$path}");

            return null;
        }

        $media = ImageOptimizer::import($path, "{$file}.webp", ['title' => $title, 'alt_fa' => $altFa, 'alt_en' => $altEn, 'uploaded_by' => null]);
        $this->line("Imported {$file} → media #{$media->id}");

        return $media->id;
    }
}
