<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'approved_id',
        'created_at'
    ];

    protected $hidden = [
        'updated_at', 'deleted_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}


