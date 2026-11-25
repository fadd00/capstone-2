<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistik extends Model
{
    use HasFactory;

    protected $table = 'statistiks';

    protected $fillable = [
        'santri_aktif',
        'asatidzah_bersanad',
        'tahun_berkhidmat',
    ];

    protected function casts(): array
    {
        return [
            'santri_aktif' => 'integer',
            'asatidzah_bersanad' => 'integer',
            'tahun_berkhidmat' => 'integer',
        ];
    }
}
