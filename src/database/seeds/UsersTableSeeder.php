<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一括削除
        User::truncate();

        //特定のデータを追加
        User::create([
            'name' => 'test1',
            'email' => 'test@test.com',
            'password' => Hash::make('testtest')
        ]);
    }
}
