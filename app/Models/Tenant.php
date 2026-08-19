<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'room_number',
        'monthly_rent',
        'due_day',
        'is_active',
        'move_in_date',
        'move_out_date',
    ];

    protected $casts = [
        'due_day' => 'integer',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getStatusAttribute()
    {
        $currentMonth = now()->startOfMonth();
        
        // Cek apakah ada transaksi lunas untuk bulan ini
        $transaction = $this->transactions()
            ->whereDate('billing_month', $currentMonth)
            ->whereNotNull('paid_at')
            ->first();

        if ($transaction) {
            return 'paid'; // Lunas untuk bulan ini
        }

        // Jika belum lunas, cek apakah sudah melewati tanggal jatuh tempo
        $dueDate = now()->day($this->due_day)->startOfDay();
        return now()->startOfDay()->gt($dueDate) ? 'overdue' : 'unpaid';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getFormattedRentAttribute()
    {
        return 'Rp' . number_format($this->monthly_rent, 0, ',', '.');
    }
}