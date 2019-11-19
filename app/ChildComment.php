<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChildComment extends Model
{
    protected $table = 'comments';

    public function author(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
