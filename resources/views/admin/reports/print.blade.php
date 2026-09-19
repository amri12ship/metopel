<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print — Laporan Absensi — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .no-print { display: none; }
        @@media print {
            body { background: #fff; }
            .no-print { display: block; }
        }
        @@media screen {
            .print-toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; }
            .print-toolbar button,
            .print-toolbar a {
                display: inline-block; padding: 0.5rem 1rem; border-radius: 0.5rem;
                font-size: 0.8rem; font-weight: 700; color: #fff; text-decoration: none; border: none; cursor: pointer;
            }
            .btn-blue { background: #2563eb; }
            .btn-gray { background: #374151; }
        }
        @@media print {
            .print-toolbar { display: none; }
        }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { font-size: 12px; color: #4b5563; margin-bottom: 12px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900 antialiased sm:p-10">
    <div class="print-toolbar no-print">
        <a class="btn-blue" href="{{ route('admin.attendance.index', request()->query()) }}">&larr; Kembali</a>
        <button class="btn-gray" onclick="window.print()">Print</button>
    </div>

    <div class="print-header">
        <h1>LAPORAN ABSENSI KARYAWAN</h1>
        <div class="meta">
            <p>Sistem Absensi Karyawan Berbasis Website</p>
            <p>Periode: <strong>{{ $title }}</strong></p>
            <p>Tanggal cetak: {{ $printedAt }} | Dicetak oleh: {{ $generatedBy }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @if ($isAbsent)
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                @else
                    <th>No</th>
                    <th>Nama</th>
                    <th>Tanggal</th>
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
                        <td>{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->isoFormat('D MMMM Y') : '—' }}</td>
                        <td>{{ $row['status'] }}</td>
                    @else
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['employee_name'] }} ({{ $row['employee_number'] }})</td>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->isoFormat('D MMMM Y') }}</td>
                        <td>{{ $row['check_in'] ?: '—' }}</td>
                        <td>{{ $row['check_out'] ?: '—' }}</td>
                        <td>{{ $row['location'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:24px;">Tidak ada data laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>