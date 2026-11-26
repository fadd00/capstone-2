<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;

    protected $table = 'santri';

    protected $fillable = [
        'nama',
        'nis',
        'tingkatan',
        'kelas',
        'wali_murid_id',
        'tanggal_lahir',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function waliMurid()
    {
        return $this->belongsTo(User::class, 'wali_murid_id');
    }

    public function ustadzPembina()
    {
        return $this->belongsToMany(User::class, 'ustadz_santri', 'santri_id', 'user_id')
            ->withPivot('id', 'created_at');
    }

    public function presensiSantri()
    {
        return $this->hasMany(PresensiSantri::class, 'santri_id');
    }

    public function setoranHafalan()
    {
        return $this->hasMany(SetoranHafalan::class, 'santri_id');
    }

    public function murojaah()
    {
        return $this->hasMany(Murojaah::class, 'santri_id');
    }

    /** Presensi, setoran, dan murojaah ter-cascade saat santri dihapus, jadi santri dengan riwayat ditolak. */
    public function deletionBlockedReason(): ?string
    {
        return $this->presensiSantri()->exists() || $this->setoranHafalan()->exists() || $this->murojaah()->exists()
            ? 'Santri ini sudah memiliki riwayat presensi, setoran, atau murojaah sehingga tidak dapat dihapus.'
            : null;
    }
}
