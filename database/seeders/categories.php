<?php

namespace Database\Seeders;

use App\Models\Photos;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class categories extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['id' => '1','name' => 'وظائف','src' => 'storage/category/job.png',],
            ['id' => '2','name' => 'اجهزة','src' => 'storage/category/phone.png',],
            ['id' => '3','name' => 'عقارات','src' => 'storage/category/realstate.png',],
            ['id' => '4','name' => 'سيارات','src' => 'storage/category/car.png',],
            ['id' => '5','name' => 'خدمات','src' => 'storage/category/general.png',],
            ['id' => '7','name' => 'اخبار','src' => 'storage/category/general.png',],
        ];
        foreach ($categories as $category) {
             $categoryData =  \App\Models\categories::create([
                'id' => $category['id'],
                'name' => $category['name'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $photo = new Photos([
                'src' => $category['src'],
            ]);
            $categoryData->photos()->save($photo);
        }
    }
}
