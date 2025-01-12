<?php

use Illuminate\Database\Seeder;
use App\Model\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {/*
        // default admin
    	DB::table('admins')->insert([
            'name' => 'Admin',
            'email' => 'admin',
            'password' => Hash::make('Comma5157!')
        ]);

        DB::disableQueryLog();
        // actual user data
        $csvFile = fopen(base_path("database/data/users.csv"), "r");
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                User::create([
                    'name' => $data['0'],
                    'username' => $data['1'],
                    'password' => Hash::make('BAT@1234')
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);

        // default user
        DB::table('users')->insert([
            'name' => 'Test',
            'username' => 'test@test.com',
            'password' => Hash::make('Comma5157!'),
            'chance' => 99
        ]);

        DB::table('users')->insert([
            'name' => 'Qualys',
            'username' => 'qualys@test.com',
            'password' => Hash::make('123456')
        ]);

        // password reset convenient user
        DB::table('users')->insert([
            'name' => 'Kent Tan',
            'username' => 'fongchan2002@gmail.com',
            'password' => Hash::make('Comma5157!')
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test 1111',
            'username' => 'test1111',
            'password' => Hash::make('BAT@1234'),
            'chance' => 99
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test 2222',
            'username' => 'test2222',
            'password' => Hash::make('BAT@1234'),
            'chance' => 99
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test 3333',
            'username' => 'test3333',
            'password' => Hash::make('BAT@1234'),
            'chance' => 99
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test 4444',
            'username' => 'test4444',
            'password' => Hash::make('BAT@1234'),
            'chance' => 99
        ]);

        // test user
        DB::table('users')->insert([
            'name' => 'Test 5555',
            'username' => 'test5555',
            'password' => Hash::make('BAT@1234'),
            'chance' => 99
        ]);

        // test user
        DB::table('spin_user')->insert([
            'name' => 'Test Spin',
            'username' => 'cnytest111',
            'password' => Hash::make('BATCNY2024'),
            'chance' => 99
        ]);

        // test user
        DB::table('spin_user')->insert([
            'name' => 'Test Spin',
            'username' => 'cnytest222',
            'password' => Hash::make('BATCNY2024'),
            'chance' => 56
        ]);

        // test user
        DB::table('spin_user')->insert([
            'name' => 'Test Spin',
            'username' => 'cnytest333',
            'password' => Hash::make('BATCNY2024'),
            'chance' => 1
        ]);
/*
        $passwordList = [
            'Kln49F', 'CD384W', 'aJe2aD', 'HmF37c', 'BXiSUh', 'FLwcS8', 'vxhTnR', 'pmhLGL', 'RfPx75', 'XjANpv', 
            'hRDdRE', 'BAZhm8', 'CYj9Qs', '2R3e5q', 't4pQnf', 'Nc9Sen', 'aPUrVK', '5ZK3p5', 'OHCweP', 'XS7mAk',
            'd5ppLD', 'gUnOGd', 'rXc7NT', 'ZT74cv', 'ydXrWN', 'xNt6cB', 'VNXCBx', 'NthNq5', 'qLgtYf', 'R9KDUZ', 
            '49QE5Z', 'YETjSJ', 'CChLrr', 'heUmKh', 'Ue7Hfn', 'fs2HrU', 'vQCzGr', 'GZnYe8', 'aVV89G', 'ZeEw3P',
            'hMjPqr', 'y9XCz9', 'KgsUfD', 'rdYqsf', 'ArrFbu', 'N8Sz8a', 'KBZqdL', 'Rnpd7c', 'sdvfUg', 'Wet2F8'
        ];
/*
        // create 50 users
        foreach (range(1,50) as $index) {
            $thisIndex = str_pad($index, 3, '0', STR_PAD_LEFT);

            DB::table('loginusers')->insert([
                'username' => 'ba'.$thisIndex,
                'password' => $passwordList[$index-1]
            ]);
        }*/
    }
}
