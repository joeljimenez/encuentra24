<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function ads(){
        return $this->hasMany(Ad::class);
    }
    public function sub_categories(){
        return $this->hasMany('App\Sub_Category')->orderBy('category_name', 'asc');
    }

    public function brands(){
        return $this->hasMany(Brand::class);
    }

    public function product_count(){
        return $this->hasMany(Ad::class)->whereStatus('1')->count();
    }
}
