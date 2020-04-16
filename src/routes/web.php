<?php
Route::get('admin','SorroController@index');

Route::get('admin/teste',function($id=null){
  return view('Sorro::teste');
});