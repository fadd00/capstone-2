<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'presensi_kegiatan';

    protected $fillable = [
        'kegiatan_pondok_id',
        'ustadz_id',
        'tanggal',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'materi',
        'latitude',
        'longitude',
        'foto_bukti',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function kegiatanPondok()
    {
        return $this->belongsTo(KegiatanPondok::class, 'kegiatan_pondok_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(User::class, 'ustadz_id');
    }

    public function presensiSantri()
    {
        return $this->hasMany(PresensiSantri::class, 'presensi_kegiatan_id');
    }
}
