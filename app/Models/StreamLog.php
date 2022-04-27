<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Carbon\Carbon;
use DateTimeInterface;

class StreamLog extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $connection = 'mongodb';
    protected $collection = 'stream_logs';

    protected $fillable = [
        'vtoken',
        'user_id',
        'site_id',
        'exp_date',
        'iu',
        'html_user_id',
        'html_domain_id',
        'html_exp_date',
        'stream_no',
        'referrer_url',
        'browser_url',
        'hls_url',
        'refresh_count',
        'ip_address',
        'agent_device',
        'agent_platform',
        'agent_browser',
        'agent_robot',
        'note'
    ];

    protected $hidden = [
        
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
