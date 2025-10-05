<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'id' => 1,
            'first_name' => 'مدیر ساختمان',
            'last_name' => 'تست',
            'mobile' => '09111111111',
            'role' => 'building_manager',
        ];
        User::create($user);

        $business = [
            'user_id' => 1,
            'name' => 'ساختمان تست',
            'name_en' => 'test',
            'phone_number' => 'test',
            'national_id' => 'test',
            'type' => 'test',
            'province' => 'test',
            'city' => 'test',
            'district' => 'test',
            'address' => 'test',
            'postal_code' => 'test',
            'email' => 'test',
            'sheba_number' => 'test',
            'card_number' => 'test',
            'national_card_image' => 'test',
            'balance' => 500000,
            'is_verified' => true,
        ];

        Business::create($business);

        $building = [
            'id' => 1,
            'building_manager_id' => 1,
        ];

        Building::create($building);

        $users = [
            [
                'id' => 2,
                'first_name' => 'a1',
                'last_name' => 'a1',
                'mobile' => '09031111111',
                'role' => 'user',
            ],
            [
                'id' => 3,
                'first_name' => 'a2',
                'last_name' => 'a2',
                'mobile' => '09032222222',
                'role' => 'user',
            ]
        ];

        $user1 = User::create($users[0]);
        $user2 = User::create($users[1]);

        $building_unit = [
            'building_id' => 1,
            'unit_number' => 'a1',
            'charge_fee' => 100000,
            'charge_debt' => 500000,
        ];
        $unit1 = BuildingUnit::create($building_unit);
        $building_unit = [
            'building_id' => 1,
            'unit_number' => 'a2',
            'charge_fee' => 100000,
            'charge_debt' => 500000,
        ];
        $unit2 = BuildingUnit::create($building_unit);

        $user1->building_units()->attach($unit1->id, ['ownership' => 'owner']);   

        $user2->building_units()->attach($unit2->id, ['ownership' => 'owner']);
        $user1->building_units()->attach($unit2->id, ['ownership' => 'renter']);

    }
}
