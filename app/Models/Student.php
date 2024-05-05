<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $fillable = ['name', 'uid', 'token_cukur'];

    public function shaveTokenPurchases()
    {
        return $this->hasMany(ShaveTokenPurchase::class);
    }


    

    protected $hidden = ['created_at', 'updated_at'];

    public function shavingQueue()
    {
        return $this->belongsTo(ShavingQueue::class)->withDefault();
    }
}
