<?php

// app/Exports/UsersExport.php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    protected $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function collection()
    {
        $userId = Auth::id();

        if ($this->role === 'system_admin') {
            return User::all();
        } elseif ($this->role === 'trust_admin') {
            return User::whereHas('trusts', function ($query) use ($userId) {
                $query->where('trust_id', $userId);
            })->get();
        } elseif ($this->role === 'hospital_admin') {
            return User::whereHas('hospitals', function ($query) use ($userId) {
                $query->where('hospital_id', $userId);
            })->get();
        }

        return collect([]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Full Name',
            'Email',
            'Role',
            // Add more headings as needed
        ];
    }
}

