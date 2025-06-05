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
            [
                'id' => '8',
                'name' => ['ar' => 'عاجل', 'en' => 'Urgent'],
                'src' => 'storage/category/job.png',
            ],
            [
                'id' => '1',
                'name' => ['ar' => 'وظائف', 'en' => 'Jobs'],
                'src' => 'storage/category/job.png',
            ],
            [
                'id' => '2',
                'name' => ['ar' => 'اجهزة', 'en' => 'Devices'],
                'src' => 'storage/category/phone.png',
            ],
            [
                'id' => '3',
                'name' => ['ar' => 'عقارات', 'en' => 'Houses'],
                'src' => 'storage/category/realstate.png',
            ],
            [
                'id' => '4',
                'name' => ['ar' => 'سيارات', 'en' => 'Cars'],
                'src' => 'storage/category/car.png',
            ],
            [
                'id' => '5',
                'name' => ['ar' => 'خدمات', 'en' => 'Services'],
                'src' => 'storage/category/general.png',
            ],
            [
                'id' => '6',
                'name' => ['ar' => 'قربي', 'en' => 'Near'],
                'src' => 'storage/category/general.png',
            ],
            [
                'id' => '7',
                'name' => ['ar' => 'فيديو', 'en' => 'Reels'],
                'src' => 'storage/category/general.png',
            ],
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
