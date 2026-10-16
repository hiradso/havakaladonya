<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrackingStage extends Model
{
    protected $fillable = ['name_fa', 'name_en', 'sort', 'is_final', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    public function name(string $locale): string
    {
        return $locale === 'en' ? $this->name_en : $this->name_fa;
    }
}
