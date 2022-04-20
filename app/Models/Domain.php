<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'domain';
    
    protected $fillable = [
        'domain',
        'thumbnail',
    ];

    protected $hidden = [
        
    ];

    public function streamDomain()
    {
        return $this->hasMany(StreamDomain::class);
    }

    public function members()
    {
        return $this->hasManyThrough(Member::class, Group::class);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }
}
