<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Murojaah extends Model
{
    use HasFactory;

    protected $table = 'murojaah';

    protected $fillable = [
        'santri_id',
        'ustadz_id',
        'tanggal',
        'juz',
        'surat',
        'ayat',
        'nilai',
        'status_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'juz' => 'integer',
            'nilai' => 'decimal:2',
            'status_selesai' => 'boolean',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(User::class, 'ustadz_id');
    }
}
