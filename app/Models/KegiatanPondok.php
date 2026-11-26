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

    /** Sesi presensi ter-cascade saat kegiatan dihapus, jadi kegiatan dengan riwayat ditolak. */
    public function deletionBlockedReason(): ?string
    {
        return $this->presensiKegiatan()->exists()
            ? 'Kegiatan ini sudah memiliki riwayat presensi sehingga tidak dapat dihapus.'
            : null;
    }

    /** Santri yang menjadi peserta kegiatan ini (sesuai tingkatan dan kelas). */
    public function santriPeserta()
    {
        return Santri::query()
            ->when($this->tingkatan && $this->tingkatan !== 'semua', fn ($q) => $q->where('tingkatan', $this->tingkatan))
            ->when($this->kelas, fn ($q) => $q->where('kelas', $this->kelas));
    }
}
