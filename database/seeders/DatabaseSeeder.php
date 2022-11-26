<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
    //   $media =\App\Models\Api\Client::find(10);
    //   return dd($media->likeMedia()->count());

        $admin =\App\Models\User::create([
                'name' => 'Test User',
                'email' => 'sarealtv@mail.com',
                'password'=>\Hash::make('123456789')
            ]);

            $profile =['client_id'=>1];
            $profile['DOB']=date('Y-m-d H:i:s' ,strtotime("1999-8-12"));
            $profile['country']="Pakistan";
            $admin->clientProfile()->create($profile);   
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
