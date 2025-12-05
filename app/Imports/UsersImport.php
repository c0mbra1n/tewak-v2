<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Skip if username already exists
        if (User::where('username', $row['username'])->exists()) {
            return null;
        }

        return new User([
            'username' => $row['username'],
            'full_name' => $row['nama_lengkap'],
            'password' => Hash::make($row['password'] ?? '123456'),
            'role' => $row['role'] ?? 'guru',
            'subject' => $row['mata_pelajaran'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'nama_lengkap' => 'required|string',
            'role' => 'nullable|in:guru,admin_kelas',
        ];
    }
}
