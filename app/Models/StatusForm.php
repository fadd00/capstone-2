<?php

namespace App\Models;

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
}
