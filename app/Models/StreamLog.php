<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class StreamLog extends Model
{
    use HasFactory, SoftDeletes;
    
    // protected $connection = 'mongodb';
    protected $connection = 'pgsql1';
    protected $table = 'stream_logs';

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
        'parent_referrer_url',
        'referrer_url',
        'browser_url',
        'hls_url',
        'refresh_count',
        'ip_address',
        'agent_device',
        'agent_platform',
        'agent_browser',
        'agent_robot',
        'note',
        'created_at_bigint',
        'created_at_date_bigint'
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
