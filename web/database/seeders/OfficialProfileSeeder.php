<?php

namespace Database\Seeders;

use App\Models\OfficialProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficialProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officials = [
            ['service_number' => 'IC-742381', 'name' => 'Arjun Rao', 'rank' => 'colonel', 'rank_val' => 3],
            ['service_number' => 'IC-618204', 'name' => 'Karthik Singh', 'rank' => 'lieutenant', 'rank_val' => 1],
            ['service_number' => 'IC-583917', 'name' => 'Daniel Thompson', 'rank' => 'captain', 'rank_val' => 2],
            ['service_number' => 'IC-406275', 'name' => 'Mukhund Varadharajan', 'rank' => 'major', 'rank_val' => 4],
            ['service_number' => 'IC-190438', 'name' => 'Abhindandhan Jai', 'rank' => 'general', 'rank_val' => 5],
            ['service_number' => 'IC-827516', 'name' => 'Michael Faraday', 'rank' => 'colonel', 'rank_val' => 3],
            ['service_number' => 'IC-674902', 'name' => 'Ravi Kumar', 'rank' => 'lieutenant', 'rank_val' => 1],
            ['service_number' => 'IC-359761', 'name' => 'Sneha Pillai', 'rank' => 'captain', 'rank_val' => 2],
        ];

        foreach ($officials as $official) {
            $user = User::query()
                ->where('service_id', $official['service_number'])
                ->orWhere('name', $official['name'])
                ->first() ?? new User;
            $user->name = $official['name'];
            $user->service_id = $official['service_number'];
            $user->email = strtolower(str_replace(' ', '.', $official['name'])).'@talos.local';
            $user->password ??= 'password';
            $user->role = 'commander';
            $user->is_active = true;
            $user->save();

            $profile = OfficialProfile::query()
                ->where('service_number', $official['service_number'])
                ->orWhere('name', $official['name'])
                ->first() ?? new OfficialProfile;
            $profile->service_number = $official['service_number'];
            $profile->fill([
                'user_id' => (string) $user->getKey(),
                'name' => $official['name'],
                'rank' => $official['rank'],
                'rank_val' => $official['rank_val'],
                'designation' => 'Military Official',
                'branch' => 'Army',
                'status' => 'active',
                'clearance_level' => $official['rank_val'],
                'specialization' => 'Command Operations',
            ]);
            $profile->save();
        }
    }
}
