<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    public const TRANSPORT_MODES = [
        'air' => 'هوایی',
        'sea' => 'دریایی',
        'road' => 'جاده‌ای',
        'rail' => 'ریلی',
        'unsure' => 'مطمئن نیستم',
    ];

    public const LOAD_TYPES = [
        'fcl' => 'کانتینر کامل (FCL)',
        'lcl' => 'خرده‌بار دریایی (LCL)',
        'ftl' => 'کامیون دربست (FTL)',
        'groupage' => 'گروپاژ / خرده‌بار',
        'unsure' => 'مطمئن نیستم',
    ];

    public const STATUSES = [
        'new' => 'جدید',
        'in_progress' => 'در حال بررسی',
        'quoted' => 'قیمت اعلام شد',
        'won' => 'تبدیل به سفارش',
        'lost' => 'منصرف شد',
    ];

    protected $fillable = [
        'locale', 'name', 'company', 'phone', 'email',
        'transport_mode', 'origin', 'destination', 'cargo_type',
        'weight_kg', 'volume_cbm', 'load_type', 'needs_clearance', 'needs_insurance',
        'description', 'attachment_path', 'attachment_name',
        'status', 'assigned_to', 'admin_notes', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'needs_clearance' => 'boolean',
            'needs_insurance' => 'boolean',
            'weight_kg' => 'decimal:2',
            'volume_cbm' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Human-friendly reference, e.g. Q-00012, given right after the row is created.
        static::created(function (QuoteRequest $quote): void {
            // forceFill: "reference" is intentionally not mass-assignable from the public form.
            $quote->forceFill(['reference' => 'Q-'.str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT)])->saveQuietly();
        });
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
