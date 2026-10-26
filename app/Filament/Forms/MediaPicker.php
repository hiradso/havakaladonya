<?php

namespace App\Filament\Forms;

use App\Models\Media;
use App\Support\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * "Choose a picture from the media library" field, with an "upload new" button next to it.
 * Stores the media id. Usage: MediaPicker::make('hero_media')->label('عکس بنر')
 */
class MediaPicker
{
    public static function make(string $name): Select
    {
        return Select::make($name)
            ->searchable()
            ->allowHtml()
            ->native(false)
            ->placeholder('انتخاب از رسانه‌ها')
            ->searchPrompt('عنوان تصویر را جستجو کنید')
            ->noSearchResultsMessage('تصویری پیدا نشد')
            ->options(fn () => self::options(Media::query()->latest()->limit(30)->get()))
            ->getSearchResultsUsing(fn (string $search) => self::options(
                Media::query()
                    ->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                        ->orWhere('alt_fa', 'like', "%{$search}%")
                        ->orWhere('alt_en', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%"))
                    ->latest()
                    ->limit(30)
                    ->get()
            ))
            ->getOptionLabelUsing(fn ($value) => ($media = Media::find($value)) ? self::label($media) : null)
            ->createOptionForm(self::uploadSchema(multiple: false))
            ->createOptionModalHeading('آپلود تصویر جدید')
            ->createOptionUsing(fn (array $data) => self::importUploads($data)[0]->id ?? null);
    }

    /** Upload form, shared by the media library page and the picker's "upload new" button. */
    public static function uploadSchema(bool $multiple): array
    {
        return [
            FileUpload::make('files')
                ->label($multiple ? 'تصویرها' : 'تصویر')
                ->multiple($multiple)
                ->maxFiles($multiple ? 30 : 1)
                ->required()
                ->image()
                ->acceptedFileTypes(ImageOptimizer::ALLOWED_MIMES)
                ->maxSize(12 * 1024)
                ->disk('local')
                ->directory('media-uploads')
                ->storeFileNamesIn('names')
                ->helperText('JPG، PNG، WebP یا GIF، هر فایل حداکثر ۱۲ مگابایت.'),
            TextInput::make('alt_fa')
                ->label('متن جایگزین فارسی (alt)')
                ->helperText($multiple ? 'اختیاری؛ برای همه‌ی عکس‌های این آپلود. بعداً برای هر عکس جدا هم قابل ویرایش است.' : 'یک جمله‌ی کوتاه درباره‌ی محتوای عکس.')
                ->maxLength(255),
            TextInput::make('alt_en')
                ->label('متن جایگزین انگلیسی (alt)')
                ->maxLength(255)
                ->extraInputAttributes(['dir' => 'ltr']),
        ];
    }

    /**
     * Optimizes the uploaded temporary files into media records and removes the temporary files.
     *
     * @return list<Media>
     */
    public static function importUploads(array $data): array
    {
        $disk = Storage::disk('local');
        $paths = array_values(array_filter((array) ($data['files'] ?? [])));
        $names = (array) ($data['names'] ?? []);
        $created = [];
        $failed = [];

        foreach ($paths as $path) {
            $name = $names[$path] ?? basename($path);
            try {
                $created[] = ImageOptimizer::import($disk->path($path), $name, [
                    'alt_fa' => $data['alt_fa'] ?? null,
                    'alt_en' => $data['alt_en'] ?? null,
                ]);
            } catch (Throwable $e) {
                report($e);
                $failed[] = $name.': '.$e->getMessage();
            } finally {
                $disk->delete($path);
            }
        }

        if ($created !== []) {
            Notification::make()->title(count($created).' تصویر آپلود و بهینه شد.')->success()->send();
        }
        if ($failed !== []) {
            Notification::make()->title('بعضی فایل‌ها آپلود نشدند')->body(implode("\n", $failed))->danger()->persistent()->send();
        }

        return $created;
    }

    /** @param  iterable<Media>  $items */
    private static function options(iterable $items): array
    {
        $options = [];
        foreach ($items as $media) {
            $options[$media->id] = self::label($media);
        }

        return $options;
    }

    private static function label(Media $media): string
    {
        $title = e($media->title ?: ($media->alt_fa ?: 'تصویر #'.$media->id));
        $src = e($media->thumbUrl());

        return '<span style="display:flex;align-items:center;gap:.6rem">'
            .'<img src="'.$src.'" alt="" loading="lazy" style="width:3.5rem;height:2.25rem;object-fit:cover;border-radius:.35rem;flex:none">'
            .'<span>'.$title.' <small style="opacity:.6">'.$media->width.'×'.$media->height.'</small></span></span>';
    }
}
