<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekapitulasi Pendaftaran PPDB</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0; font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #777; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .status-lulus { color: green; font-weight: bold; }
        .status-tidak-lulus { color: red; font-weight: bold; }
        .status-menunggu { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekapitulasi Pendaftaran Santri Baru</h2>
        <p>Pondok Pesantren Tahfizhul Qur'an Muhammadiyah Ibnu Juraimi Yogyakarta</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Nama Lengkap</th>
                <th>Jenis Kelamin</th>
                <th>NISN</th>
                <th>Asal Sekolah</th>
                <th>No WhatsApp / HP</th>
                <th>Alamat</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pendaftarans as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->jenis_kelamin }}</td>
                    <td>{{ $item->nisn ?? '-' }}</td>
                    <td>{{ $item->asal_sekolah ?? '-' }}</td>
                    <td>{{ $item->no_hp ?? '-' }}</td>
                    <td>{{ $item->alamat ?? '-' }}</td>
                    <td class="text-center">
                        @if ($item->status === 'Lulus')
                            <span class="status-lulus">Lulus</span>
                        @elseif ($item->status === 'Tidak Lulus')
                            <span class="status-tidak-lulus">Tidak Lulus</span>
                        @else
                            <span class="status-menunggu">Menunggu</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">Belum ada data pendaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
