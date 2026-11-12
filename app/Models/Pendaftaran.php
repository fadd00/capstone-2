<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendaftaran extends Model
{
    use HasFactory;

    protected $table = 'pendaftarans';

    public const ALLOWED_FILES = [
        'pas_photo',
        'bukti_transfer',
        'formulir_pendaftaran',
        'surat_pernyataan',
        'akta_kelahiran',
        'kartu_keluarga',
        'ijazah_terakhir',
        'raport_terakhir',
        'skhun',
        'sktm',
    ];

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'jenis_kelamin',
        'email',
        'no_hp',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'nisn',
        'nik_santri',
        'no_kk',
        'nama_ayah',
        'nik_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_hp_ayah',
        'nama_ibu',
        'nik_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_hp_ibu',
        'asal_sekolah',
        'alamat_asal_sekolah',
        'jumlah_hafalan',
        'prestasi',
        'pas_photo',
        'bukti_transfer',
        'formulir_pendaftaran',
        'surat_pernyataan',
        'akta_kelahiran',
        'kartu_keluarga',
        'ijazah_terakhir',
        'raport_terakhir',
        'skhun',
        'sktm',
        'berkas',
        'status',
        'tanggal_daftar',
        'santri_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_daftar' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'Menunggu');
    }

    public function scopeLulus($query)
    {
        return $query->where('status', 'Lulus');
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('status', 'Tidak Lulus');
    }
}
