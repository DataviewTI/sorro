<?php
namespace Dataview\Sorro;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(){
      $this->call(ServiceSeeder::class);
    }
}
