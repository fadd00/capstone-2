<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanPondok extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_pondok';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tingkatan',
        'kelas',
    ];

    public function ustadz()
    {
        return $this->belongsToMany(User::class, 'ustadz_kegiatan', 'kegiatan_pondok_id', 'user_id')
            ->withPivot('id', 'created_at');
    }

    public function presensiKegiatan()
    {
        return $this->hasMany(PresensiKegiatan::class, 'kegiatan_pondok_id');
    }
}
