<?php

use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questionList = [
            ['q_1', 'a_1_1', 'a_1_2', '', '', '1', '1'],
            ['q_2', 'a_2_1', 'a_2_2', 'a_2_3', '', '3', '1'],
            ['q_3', 'a_3_1', 'a_3_2', 'a_3_3', 'a_3_4', '2', '1'],
            ['q_4', 'a_4_1', 'a_4_2', '', '', '2', '1'],
            ['q_5', 'a_5_1', 'a_5_2', 'a_5_3', '', '1,2', '1'],
            ['q_6', 'a_6_1', 'a_6_2', 'a_6_3', 'a_6_4', '2', '1'],
            ['q_7', 'a_7_1', 'a_7_2', '', '', '1', '1'],
            ['q_8', 'a_8_1', 'a_8_2', '', '', '1', '1'],
            ['q_9', 'a_9_1', 'a_9_2', '', '', '2', '1'],
            ['q_10', 'a_10_1', 'a_10_2', 'a_10_3', 'a_10_4', '1', '1'],
            ['q_11', 'a_11_1', 'a_11_2', 'a_11_3', 'a_11_4', '2', '1'],
            ['q_12', 'a_12_1', 'a_12_2', 'a_12_3', 'a_12_4', '3', '1'],
            ['q_13', 'a_13_1', 'a_13_2', 'a_13_3', 'a_13_4', '4', '1'],
            ['q_14', 'a_14_1', 'a_14_2', 'a_14_3', 'a_14_4', '2', '1']
        ];

        foreach ($questionList as $question) {
            DB::table('question')->insert([
                'question' => $question[0],
                'a_1' => $question[1],
                'a_2' => $question[2],
                'a_3' => $question[3],
                'a_4' => $question[4],
                'answer' => $question[5],
                'status' => $question[6],
            ]);
        }
    }
}
