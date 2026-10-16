<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const STATUSES = [
        'new' => 'جدید',
        'answered' => 'پاسخ داده شد',
        'closed' => 'بسته شد',
    ];

    protected $fillable = ['name', 'phone', 'email', 'message', 'locale', 'ip_address', 'status', 'admin_notes'];

    protected $attributes = [
        'status' => 'new',
    ];
}
