<?php
Route::get('admin','SorroController@index');

Route::get('admin/',function($id=null){
  return view('Sorro::index');
});