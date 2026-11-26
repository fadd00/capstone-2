<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class LaporanExport extends DefaultValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings, WithMapping
{
    /** Teks berawalan "=" disimpan sebagai teks, bukan rumus (mencegah formula injection). */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value) && str_starts_with($value, '=')) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function __construct(
        protected Collection $records,
        protected string $type
    ) {}

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return match ($this->type) {
            'presensi-ustadz' => ['Tanggal', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ustadz', 'Kegiatan', 'Status'],
            'presensi-santri' => ['Tanggal', 'Santri', 'NIS', 'Tingkatan', 'Kelas', 'Kegiatan', 'Ustadz', 'Status'],
            'hafalan' => ['Tanggal', 'Santri', 'Ustadz', 'Juz', 'Surat', 'Ayat', 'Nilai', 'Status Selesai'],
            'murojaah' => ['Tanggal', 'Santri', 'Ustadz', 'Juz', 'Surat', 'Ayat', 'Nilai', 'Status Selesai'],
            default => ['Data'],
        };
    }

    public function map($row): array
    {
        return match ($this->type) {
            'presensi-ustadz' => [
                $row->tanggal?->format('Y-m-d') ?? $row->tanggal,
                $row->hari,
                $row->jam_mulai,
                $row->jam_selesai,
                $row->ustadz?->name ?? '-',
                $row->kegiatanPondok?->nama_kegiatan ?? '-',
                $row->status,
            ],
            'presensi-santri' => [
                $row->presensiKegiatan?->tanggal?->format('Y-m-d') ?? $row->presensiKegiatan?->tanggal ?? '-',
                $row->santri?->nama ?? '-',
                $row->santri?->nis ?? '-',
                $row->santri?->tingkatan ?? '-',
                $row->santri?->kelas ?? '-',
                $row->presensiKegiatan?->kegiatanPondok?->nama_kegiatan ?? '-',
                $row->presensiKegiatan?->ustadz?->name ?? '-',
                $row->status,
            ],
            'hafalan' => [
                $row->tanggal?->format('Y-m-d') ?? $row->tanggal,
                $row->santri?->nama ?? '-',
                $row->ustadz?->name ?? '-',
                $row->juz,
                $row->surat,
                $row->ayat,
                $row->nilai,
                $row->status_selesai ? 'Selesai' : 'Belum Selesai',
            ],
            'murojaah' => [
                $row->tanggal?->format('Y-m-d') ?? $row->tanggal,
                $row->santri?->nama ?? '-',
                $row->ustadz?->name ?? '-',
                $row->juz,
                $row->surat,
                $row->ayat,
                $row->nilai,
                $row->status_selesai ? 'Selesai' : 'Belum Selesai',
            ],
            default => (array) $row,
        };
    }
}
