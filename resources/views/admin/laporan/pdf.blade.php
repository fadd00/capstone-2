<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 7px 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PPTQ IBNU JURAIMI YOGYAKARTA</h1>
        <p>Laporan Resmi Sistem Informasi Akademik & Pondok Pesantren</p>
        <p><strong>{{ $title }}</strong> | Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    @if($laporan === 'presensi-ustadz')
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 15%;">Hari & Jam</th>
                    <th style="width: 20%;">Ustadz</th>
                    <th style="width: 25%;">Kegiatan Pondok</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->hari ?? '-' }} ({{ $row->jam_mulai }} - {{ $row->jam_selesai }})</td>
                        <td>{{ $row->ustadz->name ?? '-' }}</td>
                        <td>{{ $row->kegiatanPondok->nama_kegiatan ?? '-' }}</td>
                        <td class="text-center">{{ strtoupper($row->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($laporan === 'presensi-santri')
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 20%;">Nama Santri</th>
                    <th style="width: 10%;">NIS</th>
                    <th style="width: 13%;">Tingkatan</th>
                    <th style="width: 20%;">Kegiatan Pondok</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->presensiKegiatan?->tanggal ? \Carbon\Carbon::parse($row->presensiKegiatan->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->santri->nama ?? '-' }}</td>
                        <td>{{ $row->santri->nis ?? '-' }}</td>
                        <td>{{ strtoupper($row->santri->tingkatan ?? '-') }} (Kelas {{ $row->santri->kelas ?? '-' }})</td>
                        <td>{{ $row->presensiKegiatan?->kegiatanPondok?->nama_kegiatan ?? '-' }}</td>
                        <td class="text-center">{{ strtoupper($row->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($laporan === 'hafalan')
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 22%;">Nama Santri</th>
                    <th style="width: 20%;">Ustadz Pembina</th>
                    <th style="width: 21%;">Surat & Ayat</th>
                    <th style="width: 8%;">Juz</th>
                    <th style="width: 12%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->santri->nama ?? '-' }}</td>
                        <td>{{ $row->ustadz->name ?? '-' }}</td>
                        <td>{{ $row->surat }} (Ayat {{ $row->ayat }})</td>
                        <td class="text-center">{{ $row->juz }}</td>
                        <td class="text-center">{{ $row->nilai ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($laporan === 'murojaah')
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 22%;">Nama Santri</th>
                    <th style="width: 20%;">Ustadz Pembina</th>
                    <th style="width: 21%;">Surat & Ayat</th>
                    <th style="width: 8%;">Juz</th>
                    <th style="width: 12%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->santri->nama ?? '-' }}</td>
                        <td>{{ $row->ustadz->name ?? '-' }}</td>
                        <td>{{ $row->surat }} (Ayat {{ $row->ayat }})</td>
                        <td class="text-center">{{ $row->juz }}</td>
                        <td class="text-center">{{ $row->nilai ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Total Rekaman: {{ count($records) }} Baris</p>
    </div>
</body>
</html>
