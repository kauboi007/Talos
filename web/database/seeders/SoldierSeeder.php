<?php

namespace Database\Seeders;

use App\Models\Soldier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SoldierSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'TalosDemo!2026';

    private const BLOOD_GROUPS = [
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-',
    ];

    private const STREETS = [
        'Anna Nagar',
        'Gandhi Road',
        'Nehru Street',
        'Lake View Road',
        'Station Road',
        'Temple Street',
    ];

    private const CITIES = [
        'Chennai',
        'Avadi',
        'Coimbatore',
        'Thanjavur',
        'Vellore',
        'Madurai',
    ];

    /*
     * Person format:
     * [full name, rank, optional role]
     *
     * All personnel have accounts.
     * Ordinary personnel receive the "soldier" role.
     */
    private const SITES = [
        'Avadi' => [
            ['Karthik Singh', 'Lieutenant'],
            ['Aditya Menon', 'General', 'base_admin'],
            ['Nisha Verma', 'Captain'],
            ['Sanjay Iyer', 'Colonel'],
            ['Farhan Ahmed', 'Major'],
            ['Divya Krishnan', 'Lieutenant'],
            ['Vivek Reddy', 'Captain'],
            ['Anil Deshmukh', 'Colonel'],
            ['Kavya Raman', 'Major'],
            ['Rahul Nair', 'Lieutenant'],
            ['Ishita Bose', 'Captain'],
            ['Manoj Pillai', 'Colonel'],
            ['Aarav Joshi', 'Major'],
            ['Priya Menon', 'Lieutenant'],
            ['Ritesh Kulkarni', 'Captain'],
            ['Megha Rao', 'Colonel'],
            ['Kiran Shah', 'Major'],
            ['Siddharth Das', 'Lieutenant'],
            ['Neha Kapoor', 'Captain'],
            ['Varun Reddy', 'Colonel'],
        ],

        'Wellington' => [
            ['Arvind Narayanan', 'General', 'base_admin'],
            ['Riya Thomas', 'Lieutenant'],
            ['Vikram Sethi', 'Captain'],
            ['Pooja Nambiar', 'Colonel'],
            ['Akash Bhat', 'Major'],
            ['Sneha Kulkarni', 'Lieutenant'],
            ['Nitin Rao', 'Captain'],
            ['Aditi Krishnan', 'Colonel'],
            ['Kunal Mehta', 'Major'],
            ['Suresh Pillai', 'Lieutenant'],
            ['Maya Iyer', 'Captain'],
            ['Gautam Das', 'Colonel'],
            ['Tara Menon', 'Major'],
            ['Rohan Kapoor', 'Lieutenant'],
            ['Leena Shah', 'Captain'],
            ['Ajay Varma', 'Colonel'],
            ['Deepa Nair', 'Major'],
            ['Harsh Venkatesh', 'Lieutenant'],
            ['Ira Bose', 'Captain'],
            ['Milan Joseph', 'Colonel'],
        ],

        'INS Rajali' => [
            ['Vikram Chandran', 'General', 'base_admin'],
            ['Anand Krishnan', 'Lieutenant'],
            ['Sonal Gupta', 'Captain'],
            ['Karthik Rao', 'Colonel'],
            ['Mira Thomas', 'Major'],
            ['Devika Nair', 'Lieutenant'],
            ['Arjun Bhat', 'Captain'],
            ['Lakshmi Menon', 'Colonel'],
            ['Ravi Sethi', 'Major'],
            ['Naveen Shah', 'Lieutenant'],
            ['Sana Iyer', 'Captain'],
            ['Pranav Das', 'Colonel'],
            ['Keerthi Pillai', 'Major'],
            ['Abhishek Bose', 'Lieutenant'],
            ['Janani Rao', 'Captain'],
            ['Tarun Mehta', 'Colonel'],
            ['Nandita Sen', 'Major'],
            ['Rahul Varma', 'Lieutenant'],
            ['Sanjana Nair', 'Captain'],
            ['Yash Kapoor', 'Colonel'],
        ],

        'Ramanathapuram' => [
            ['Suresh Narayan', 'General', 'base_admin'],
            ['Madhav Iyer', 'Lieutenant'],
            ['Shreya Menon', 'Captain'],
            ['Vijay Nair', 'Colonel'],
            ['Ritu Shah', 'Major'],
            ['Manish Rao', 'Lieutenant'],
            ['Divya Bose', 'Captain'],
            ['Pradeep Thomas', 'Colonel'],
            ['Anjali Das', 'Major'],
            ['Kishore Pillai', 'Lieutenant'],
            ['Nikhil Sen', 'Captain'],
            ['Swathi Varma', 'Colonel'],
            ['Rakesh Mehta', 'Major'],
            ['Amrita Kapoor', 'Lieutenant'],
            ['Sanjay Bhat', 'Captain'],
            ['Ishaan Joseph', 'Colonel'],
            ['Pallavi Raman', 'Major'],
            ['Girish Nambiar', 'Lieutenant'],
            ['Neeraj Gupta', 'Captain'],
            ['Meera Sethi', 'Colonel'],
        ],

        'St. Thomas Mount' => [
            ['Abhindandhan Jai', 'General', 'system_admin'],
            ['Ravi Kumar', 'Lieutenant'],
            ['Vishal Menon', 'Captain'],
            ['Asha Nair', 'Colonel', 'hq_admin'],
            ['Sandeep Rao', 'Major'],
            ['Nithya Iyer', 'Lieutenant'],
            ['Rohan Das', 'Captain'],
            ['Kavitha Shah', 'Colonel'],
            ['Gokul Pillai', 'Major'],
            ['Tanya Bose', 'Lieutenant'],
            ['Adarsh Varma', 'Captain'],
            ['Snehal Mehta', 'Colonel'],
            ['Arun Thomas', 'Major'],
            ['Priyanka Sen', 'Lieutenant'],
            ['Bala Krishnan', 'Captain'],
            ['Mohan Kapoor', 'Colonel'],
            ['Reshma Nambiar', 'Major'],
            ['Siddharth Raman', 'Lieutenant'],
            ['Geeta Joseph', 'Captain'],
            ['Varsha Gupta', 'Colonel'],
        ],

        'Chengalpet' => [
            ['Daniel Thompson', 'Captain'],
            ['Devendra Prasad', 'General', 'hq_admin'],
            ['Harini Rao', 'Lieutenant'],
            ['Dinesh Menon', 'Captain'],
            ['Sanjana Iyer', 'Colonel'],
            ['Prakash Nair', 'Major'],
            ['Monica Das', 'Lieutenant'],
            ['Abhinav Shah', 'Captain'],
            ['Sowmya Pillai', 'Colonel'],
            ['Vivek Thomas', 'Major'],
            ['Radhika Bose', 'Lieutenant'],
            ['Naveen Varma', 'Captain'],
            ['Anusha Mehta', 'Colonel'],
            ['Kiran Kapoor', 'Major'],
            ['Deepak Sen', 'Lieutenant'],
            ['Mitali Raman', 'Captain'],
            ['Surya Joseph', 'Colonel'],
            ['Nandini Gupta', 'Major'],
            ['Ashwin Sethi', 'Lieutenant'],
            ['Pavithra Nambiar', 'Captain'],
        ],
    ];

    /*
     * Original personnel assigned to depot/outpost sites.
     * Their checkpoint assignments are not stored here.
     */
    private const ADDITIONAL = [
        ['Arjun Rao', 'Colonel', 'TAL-OF-0001'],
        ['Mukhund Varadharajan', 'Major', 'TAL-OF-0004'],
        ['Michael Faraday', 'Colonel', 'TAL-OF-0006'],
        ['Sneha Pillai', 'Captain', 'TAL-OF-0008'],
    ];

    /*
     * Stable demo IDs for the original personnel included
     * in the six main site groups.
     *
     * These are not the original Neo4j UUIDs.
     */
    private const ORIGINAL_IDS = [
        'Karthik Singh' => 'TAL-OF-0002',
        'Daniel Thompson' => 'TAL-OF-0003',
        'Abhindandhan Jai' => 'TAL-OF-0005',
        'Ravi Kumar' => 'TAL-OF-0007',
    ];

    public function run(): void
    {
        $sequence = 0;
        $generatedId = 9;

        foreach (self::SITES as $siteName => $people) {
            if (count($people) !== 20) {
                throw new \RuntimeException(
                    "Expected 20 personnel at {$siteName}; found ".
                        count($people)
                );
            }

            foreach ($people as $person) {
                [$fullName, $rank] = $person;

                $role = $person[2] ?? 'soldier';

                $serviceId =
                    self::ORIGINAL_IDS[$fullName] ??
                    sprintf('TAL-OF-%04d', $generatedId++);

                $this->seedPerson(
                    fullName: $fullName,
                    serviceId: $serviceId,
                    rank: $rank,
                    role: $role,
                    sequence: $sequence++
                );
            }
        }

        foreach (self::ADDITIONAL as [$name, $rank, $serviceId]) {
            $this->seedPerson(
                fullName: $name,
                serviceId: $serviceId,
                rank: $rank,
                role: 'soldier',
                sequence: $sequence++
            );
        }
    }

    private function seedPerson(
        string $fullName,
        string $serviceId,
        string $rank,
        string $role,
        int $sequence
    ): void {
        $nameParts = preg_split('/\s+/', trim($fullName), 2);

        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $email = $role === 'system_admin'
            ? 'admin@talos.test'
            : $this->emailFor($fullName);

        $user = User::create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => sprintf('9%09d', $sequence + 1),
            'email' => $email,
            'password' => self::DEMO_PASSWORD,
            'role' => $role,
            'status' => 'active',
        ]);

        Soldier::create([
            'service_id' => $serviceId,
            'user_id' => $user->getKey(),
            'dob' => now()
                ->subYears(22 + ($sequence % 20))
                ->subDays(($sequence * 17) % 365)
                ->toDateString(),
            'bloodGroup' => self::BLOOD_GROUPS[
                $sequence % count(self::BLOOD_GROUPS)
            ],
            'street' => self::STREETS[
                $sequence % count(self::STREETS)
            ],
            'city' => self::CITIES[
                $sequence % count(self::CITIES)
            ],
            'state' => 'Tamil Nadu',
            'pincode' => sprintf('%06d', 600001 + ($sequence % 999)),
            'rank' => $rank,
        ]);
    }

    private function emailFor(string $fullName): string
    {
        return strtolower(str_replace(' ', '.', trim($fullName))).
            '@talos.test';
    }
}
