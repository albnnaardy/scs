<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShavingQueue extends Model
{
    use HasFactory;

    protected $table = 'shaving_queues';

    protected $fillable = ['queue_number', 'token_type', 'student_id'];

    

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected $hidden = ['created_at', 'updated_at'];

    public function shaveTokenPurchase()
    {
        return $this->hasOne(ShaveTokenPurchase::class);
    }

    // public function getTypeAttribute($value)
    // {
    //     return $value === 'reguler' ? 'Reguler' : 'VIP';
    // }

    public static function getConstants()
    {
        return ['reguler', 'vip'];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->type === 'vip') {
                $model->queue_number = null;

                $currentQueueNumber = ShavingQueue::where('student_id', $model->student_id)->first();

                if ($currentQueueNumber) {
                    $currentQueueNumber->position = $currentQueueNumber->position + 1;
                    $currentQueueNumber->save();
                }
            }
        });
    }
}
