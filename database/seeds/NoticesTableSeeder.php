<?php

use Illuminate\Database\Seeder;

class NoticesTableSeeder extends Seeder
{
    const NUM_NOTICES = 10;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Notice', self::NUM_NOTICES)->create();
    }
}
