<?php

// app/Exports/UsersExport.php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use Exportable;
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 30,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
        ];
    }

    public function collection()
    {
        $userId = $this->user->id;
        if ($this->user->is_system_admin) {
            return User::systemAdminUsers($userId)->get();
        } elseif ($this->user->is_trust_admin) {
            return User::trustAdminUsers($userId)->get();
        } elseif ($this->user->is_hospital_admin) {
            return User::hospitalAdminUsers($userId)->get();
        }
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Trust',
            'Role',
            'Speciality',
            'Sub Speciality',
            'Hospital',
        ];
    }

    public function map($row): array
    {
        $hospitals = $row->getHospitals->pluck('hospital_name')->implode(', ');
        return [
            $row->full_name,
            $row->user_email,
            $row->trusts->value('trust_name') ?? '',
            $row->role->role_name,
            $row->specialityDetail->value('speciality_name') ?? '',
            $row->subSpecialityDetail->value('sub_speciality_name') ?? '',
            $hospitals ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
