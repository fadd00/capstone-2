<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keunggulan extends Model
{
    use HasFactory;

    protected $table = 'keunggulans';

    protected $fillable = [
        'judul',
        'deskripsi_singkat',
        'deskripsi_detail',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }
}
