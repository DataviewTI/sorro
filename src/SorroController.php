<?php
namespace Dataview\Sorro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SorroController extends Controller
{

  static function getServices(){
    return DB::table('services')
    ->select('id','service','ico','alias','trans','description')
    ->orderBy('order')
    ->distinct()
    ->get();
  }

}
