<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(UsersTableSeeder::class);
//        $this->call(RolesTableSeeder::class);
//        $this->call(RegionsTableSeeder::class);
//        $this->call(DistrictsTableSeeder::class);
//        $this->call(CitizenSeeder::class);
//        $this->call(socialStatusSeeder::class);
        $this->call(ApplicationsSeeder::class);
//        $this->call(StatusesTableSeeder::class);
//        $this->call(DenyReasonsTableSeeder::class);


    }
}
