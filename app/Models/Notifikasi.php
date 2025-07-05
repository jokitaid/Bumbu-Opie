<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tipe',
        'data',
        'dibaca_pada',
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'data' => 'array',
        'dibaca_pada' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Check if notification is read
     */
    public function getIsReadAttribute()
    {
        return !is_null($this->dibaca_pada);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['dibaca_pada' => now()]);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\NotifikasiFactory::new();
    }
}
