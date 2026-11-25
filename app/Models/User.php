<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Alasan akun tidak boleh dihapus, atau null jika aman. Foreign key presensi,
     * setoran, dan murojaah memakai cascade, jadi menghapus pemilik riwayat akan
     * ikut menghapus data santri.
     */
    public function deletionBlockedReason(): ?string
    {
        if ($this->hasRole('admin') && self::role('admin')->count() <= 1) {
            return 'Tidak dapat menghapus satu-satunya administrator.';
        }

        if ($this->presensiKegiatan()->exists() || $this->setoranHafalan()->exists() || $this->murojaah()->exists()) {
            return 'Akun ini memiliki riwayat presensi, setoran, atau murojaah sehingga tidak dapat dihapus. '
                .'Cabut penugasannya atau ganti password untuk menonaktifkan akun.';
        }

        return null;
    }

    // Relasi Wali Murid -> Santri
    public function santriAsWali()
    {
        return $this->hasMany(Santri::class, 'wali_murid_id');
    }

    public function santri()
    {
        return $this->santriAsWali();
    }

    // Relasi Ustadz -> Kegiatan Pondok (Many-to-Many)
    public function kegiatanPondok()
    {
        return $this->belongsToMany(KegiatanPondok::class, 'ustadz_kegiatan', 'user_id', 'kegiatan_pondok_id')
            ->withPivot('id', 'created_at');
    }

    // Relasi Ustadz -> Santri Binaan (Many-to-Many)
    public function santriBinaan()
    {
        return $this->belongsToMany(Santri::class, 'ustadz_santri', 'user_id', 'santri_id')
            ->withPivot('id', 'created_at');
    }

    // Relasi Ustadz -> Presensi Kegiatan (Has Many)
    public function presensiKegiatan()
    {
        return $this->hasMany(PresensiKegiatan::class, 'ustadz_id');
    }

    // Relasi Ustadz -> Setoran Hafalan (Has Many)
    public function setoranHafalan()
    {
        return $this->hasMany(SetoranHafalan::class, 'ustadz_id');
    }

    // Relasi Ustadz -> Murojaah (Has Many)
    public function murojaah()
    {
        return $this->hasMany(Murojaah::class, 'ustadz_id');
    }
}
