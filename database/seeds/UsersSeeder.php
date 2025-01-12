<?php

use Illuminate\Database\Seeder;
use App\Model\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();
/*
        // test user
        DB::table('users')->insert([
            'name' => 'Comma Test',
            'username' => 'comma',
            'password' => Hash::make('Comma5157!'),
            'chance' => 99,
            'status' => 1
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test Spin 1',
            'username' => 'test111',
            'password' => Hash::make('BATQ22024'),
            'chance' => 99,
            'status' => 1
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test Spin 2',
            'username' => 'test222',
            'password' => Hash::make('BATQ22024'),
            'chance' => 56,
            'status' => 1
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test Spin 3',
            'username' => 'test333',
            'password' => Hash::make('BATQ22024'),
            'chance' => 1,
            'status' => 1
        ]);*/

        // actual user data
        $csvFile = fopen(base_path("database/data/spin_users.csv"), "r");
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                User::create([
                    'username' => $data['0'],
                    'name' => $data['0'],
                    'chance' => $data['1'],
                    'max_chance' => $data['1'],
                    'batch' => $data['2'],
                    'password' => Hash::make('BATQ22024'),
                    'status' => 1,
                    'available' => 1
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}