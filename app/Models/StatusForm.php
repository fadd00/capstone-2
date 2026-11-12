<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusForm extends Model
{
    use HasFactory;

    protected $table = 'status_forms';

    protected $fillable = [
        'status',
        'tanggal_buka',
        'tanggal_tutup',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_buka' => 'date',
            'tanggal_tutup' => 'date',
        ];
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'Buka') {
            return false;
        }

        $today = Carbon::today();

        if ($this->tanggal_buka && $today->lt($this->tanggal_buka)) {
            return false;
        }

        if ($this->tanggal_tutup && $today->gt($this->tanggal_tutup)) {
            return false;
        }

        return true;
    }
}
