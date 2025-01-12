<?php

use Illuminate\Database\Seeder;

class PrizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prizeList = [
            ['01 Prize', 0, 5000, 'normal', 1, 10],
            ['02 Prize', 5001, 15000, 'normal', 1, 3885],
            ['03 Prize', 15001, 25000, 'normal', 1, 11655],
            ['04 Prize', 25001, 350000, 'normal', 1, 62160],
            ['05 Prize', 35001, 450000, 'normal', 1, 62160],
            ['06 Prize', 45001, 550000, 'normal', 1, 62160],
            ['07 Prize', 55001, 650000, 'normal', 1, 62160],
            ['08 Prize', 65001, 750000, 'normal', 1, 62160],
            ['09 Prize', 75001, 850000, 'normal', 1, 62160],
            ['10 Prize', 85001, 100000, 'normal', 1, 62160]
        ];

        foreach ($prizeList as $prize) {
            DB::table('prize')->insert([
            	'name' => $prize[0],
				'rate_min' => $prize[1],
				'rate_max' => $prize[2],
				'type' => $prize[3],
				'is_prize' => $prize[4],
				'quantity' => $prize[5]
            ]);
        }
    }
}
