<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\Santri;
use DateTimeInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PendaftarPromotionService
{
    /**
     * Promote a verified 'Lulus' applicant to an active Santri record.
     *
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function promote(
        Pendaftaran $pendaftar,
        string $nis,
        string $tingkatan,
        string $kelas,
        ?int $waliMuridId = null
    ): Santri {
        if ($pendaftar->status !== 'Lulus') {
            throw new DomainException('Pendaftar belum dinyatakan Lulus.');
        }

        if ($pendaftar->santri_id !== null) {
            throw new DomainException('Pendaftar sudah dipromosikan sebelumnya.');
        }

        return DB::transaction(function () use ($pendaftar, $nis, $tingkatan, $kelas, $waliMuridId): Santri {
            if (Santri::where('nis', $nis)->exists()) {
                throw new InvalidArgumentException("NIS {$nis} sudah terdaftar.");
            }

            $santri = Santri::create([
                'nama' => $pendaftar->nama_lengkap,
                'nis' => $nis,
                'tingkatan' => $tingkatan,
                'kelas' => $kelas,
                'wali_murid_id' => $waliMuridId,
            ]);

            if ($pendaftar->tanggal_lahir) {
                $birthDate = $pendaftar->tanggal_lahir instanceof DateTimeInterface
                    ? $pendaftar->tanggal_lahir->format('Y-m-d')
                    : (is_string($pendaftar->tanggal_lahir) ? substr($pendaftar->tanggal_lahir, 0, 10) : $pendaftar->tanggal_lahir);

                DB::table('santri')->where('id', $santri->id)->update(['tanggal_lahir' => $birthDate]);
                $santri->refresh();
            }

            $pendaftar->update(['santri_id' => $santri->id]);

            return $santri;
        });
    }
}
