<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Comment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(categories::class);
        $this->call(subcategories::class);
        $this->call(CountriesSeeder::class);
        $this->call(CitiesSeeder::class);
        User::factory(50)->create();
        ServicePost::factory(400)->create();
        Comment::factory(400)->create();
    }
}
