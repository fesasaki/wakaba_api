<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Music extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'musics';

    protected $fillable = [
        'uuid', 
        'title',
        'creator_id',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
