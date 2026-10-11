<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->whereIn('service_id', ['OPR-COMMANDER-01'])
            ->delete();

        $user = User::query()
            ->where('service_id', 'IC-000001')
            ->orWhere('name', 'Talos Commander')
            ->first() ?? new User;
        $user->name = 'Talos Commander';
        $user->service_id = 'IC-000001';
        $user->email = 'commander@talos.local';
        $user->password = Hash::make('password');
        $user->role = 'commander';
        $user->is_active = true;
        $user->save();
    }
}
