<?php
namespace Dataview\Sorro;

use Illuminate\Database\Seeder;
use Dataview\Sorro\Service;

class ServiceSeeder extends Seeder
{
    public function run(){

      if(!Service::where('service','Dashboard')->exists()){
        Service::insert([
          'service' => "Dashboard",
          'alias' => 'dash',
          'trans' => 'Painel',
          'ico' => 'ico-dashboard',
          'description' => 'Sorro Dashboard',
          'order' => 0
          ]);
      }
   }
}
