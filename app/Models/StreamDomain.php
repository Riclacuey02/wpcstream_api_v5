<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class StreamDomain extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'stream_domain';
    
    protected $fillable = [
        'domain_id',
        'stream_id',
        'status',
    ];

    protected $hidden = [
        
    ];

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
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
