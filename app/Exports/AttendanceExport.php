<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly array $rows,
    ) {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(): Collection
    {
        return collect($this->rows);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Karyawan',
            'Nomor Karyawan',
            'Department',
            'Tanggal',
            'Check-in',
            'Check-out',
            'Lokasi',
            'Status',
            'Latitude Check-in',
            'Longitude Check-in',
            'Latitude Check-out',
            'Longitude Check-out',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    public function map($row): array
    {
        return [
            $row['no'] ?? '',
            $row['employee_name'],
            $row['employee_number'],
            $row['department'],
            $row['date'] ?? '',
            $row['check_in'] ?? '',
            $row['check_out'] ?? '',
            $row['location'],
            $row['status'],
            $row['check_in_latitude'] ?? '',
            $row['check_in_longitude'] ?? '',
            $row['check_out_latitude'] ?? '',
            $row['check_out_longitude'] ?? '',
        ];
    }
}