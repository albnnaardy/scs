<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShaveTokenPurchase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function shavingQueue()
    {
        return $this->belongsTo(ShavingQueue::class)->withDefault();
    }

    protected $hidden = ['created_at', 'updated_at'];

    public function getQueue()
    {
        if ($this->relationLoaded('shavingQueue')) {
            return $this->shavingQueue->queue_number;
        } else {
            return $this->shavingQueue()->where('type', 'reguler')->first()->queue_number ?? null;
        }
    }
}
