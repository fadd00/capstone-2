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

    // Relasi Calon Santri -> Pendaftaran PPDB (Has One)
    public function pendaftaran()
    {
        return $this->hasOne(Pendaftaran::class, 'user_id');
    }
}
