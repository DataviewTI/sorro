<?php
namespace Dataview\Sorro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SorroController extends Controller
{

  function index(){
   return view('Sorro::index');
  }

}
