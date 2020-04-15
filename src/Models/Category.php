<?php

namespace Dataview\Sorro

use Dataview\Sorro\IOModel;
use Dataview\Sorro\Category;

class Category extends IOModel
{
	protected $fillable = ['category_id','service_id','category','category_slug','erasable','description','config','order','erasable'];
	
  protected $casts = [
    'config' => 'array',
  ];


  public function service(){
      return $this->belongsTo('Dataview\Sorro\Service', 'service_id');
  }
  
  public function category(){
      return $this->belongsTo('Dataview\Sorro\Category', 'category_id')->whereNull('category_id');
  }

  public function maincategory(){
      return $this->belongsTo('Dataview\Sorro\Category', 'category_id');
  }

  public function subcategories(){
      return $this->hasMany('Dataview\Sorro\Category', 'category_id');
  }

  // recursive
  public function childCategories(){
      return $this->subcategories()->with('childCategories');
  }

}