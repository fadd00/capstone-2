<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSantri extends Model
{
    use HasFactory;

    protected $table = 'presensi_santri';

    protected $fillable = [
        'presensi_kegiatan_id',
        'santri_id',
        'status',
    ];

    public function presensiKegiatan()
    {
        return $this->belongsTo(PresensiKegiatan::class, 'presensi_kegiatan_id');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
