<?php

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
        $this->call(BackendPermission::class);
        $this->call(BackendMenu::class);
        $this->call(FrontendPermission::class);
        $this->call(FrontendMenu::class);
        $this->call(DialplanTableSeeder::class);
    }
}
