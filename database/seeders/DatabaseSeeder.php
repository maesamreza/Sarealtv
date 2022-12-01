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
            \App\Models\User::create([
                'name' => 'Test User',
                'email' => 'sarealtv@mail.com',
                'password'=>\Hash::make('123456789')
            ]);


            \App\Models\MediaType::insert([
           ['name'=>'Trailers'],
           ['name'=>'Tv Shows'],
           ['name'=>'Movies']
            ]);

            \App\Models\AdminMediaCategory::insert([
                
                ['media_type_id'=>1,'category'=>'Movies'],
                ['media_type_id'=>1,'category'=>'Tv Shows'],
                ['media_type_id'=>2,'category'=>'Popular'],
                ['media_type_id'=>2,'category'=>'Action'],
                ['media_type_id'=>2,'category'=>'Comedy'],
                ['media_type_id'=>3,'category'=>'Popular'],
                ['media_type_id'=>3,'category'=>'Action'],
                ['media_type_id'=>3,'category'=>'Comedy'],
            ]);}


            public function getType(){
                
                $types =\App\Models\MediaType::all(); 
            }
}
