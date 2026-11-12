<?php

namespace App\Exports;

use App\Models\Pendaftaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PpdbExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(public ?string $status = null) {}

    public function collection()
    {
        $query = Pendaftaran::query();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Jenis Kelamin',
            'Email',
            'No HP',
            'Tempat Lahir',
            'Tanggal Lahir',
            'NIK Santri',
            'NISN',
            'No KK',
            'Alamat',
            'Nama Ayah',
            'NIK Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'No HP Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'No HP Ibu',
            'Asal Sekolah',
            'Alamat Asal Sekolah',
            'Jumlah Hafalan',
            'Prestasi',
            'Status',
        ];
    }

    /**
     * @param  Pendaftaran  $pendaftaran
     */
    public function map($pendaftaran): array
    {
        return [
            $pendaftaran->nama_lengkap,
            $pendaftaran->jenis_kelamin,
            $pendaftaran->email,
            $pendaftaran->no_hp,
            $pendaftaran->tempat_lahir,
            $pendaftaran->tanggal_lahir instanceof \DateTimeInterface
                ? $pendaftaran->tanggal_lahir->format('Y-m-d')
                : $pendaftaran->tanggal_lahir,
            $pendaftaran->nik_santri,
            $pendaftaran->nisn,
            $pendaftaran->no_kk,
            $pendaftaran->alamat,
            $pendaftaran->nama_ayah,
            $pendaftaran->nik_ayah,
            $pendaftaran->pekerjaan_ayah,
            $pendaftaran->penghasilan_ayah,
            $pendaftaran->no_hp_ayah,
            $pendaftaran->nama_ibu,
            $pendaftaran->nik_ibu,
            $pendaftaran->pekerjaan_ibu,
            $pendaftaran->penghasilan_ibu,
            $pendaftaran->no_hp_ibu,
            $pendaftaran->asal_sekolah,
            $pendaftaran->alamat_asal_sekolah,
            $pendaftaran->jumlah_hafalan,
            $pendaftaran->prestasi,
            $pendaftaran->status,
        ];
    }
}
