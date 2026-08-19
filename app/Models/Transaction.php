<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'billing_month',
        'amount',
        'due_date',
        'paid_at',
        'reminder_h3_sent',
        'reminder_h1_sent',
        'reminder_due_sent',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',

        'reminder_h3_sent' => 'boolean',
        'reminder_h1_sent' => 'boolean',
        'reminder_due_sent' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->id ??= (string) Str::uuid();
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}