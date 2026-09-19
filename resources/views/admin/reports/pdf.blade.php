<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { font-size: 10px; color: #4b5563; margin-bottom: 14px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { margin-top: 14px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>LAPORAN ABSENSI KARYAWAN</h1>
    <div class="meta">
        <p>Sistem Absensi Karyawan Berbasis Website</p>
        <p>Periode: <strong>{{ $title }}</strong></p>
        <p>Tanggal cetak: {{ $printedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @if ($isAbsent)
                    <th style="width:30px;">No</th>
                    <th>Nama Karyawan</th>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                @else
                    <th style="width:30px;">No</th>
                    <th>Nama</th>
                    <th style="width:80px;">Tanggal</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    @if ($isAbsent)
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['employee_name'] }}</td>
                        <td>{{ $row['employee_number'] }}</td>
                        <td>{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d-m-Y') : '—' }}</td>
                        <td>{{ $row['status'] }}</td>
                    @else
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['employee_name'] }}<br><span style="color:#6b7280;font-size:8px;">{{ $row['employee_number'] }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                        <td>{{ $row['check_in'] ?: '—' }}</td>
                        <td>{{ $row['check_out'] ?: '—' }}</td>
                        <td>{{ $row['location'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;">Tidak ada data laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Laporan ini dihasilkan otomatis oleh sistem. Dokumen tidak memerlukan tanda tangan selama periode data absensi valid.</div>
</body>
</html>