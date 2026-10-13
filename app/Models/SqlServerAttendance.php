<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SqlServerAttendance extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CHECKINOUT';
    public $timestamps = false;

    protected $fillable = [
        'USERID',
        'CHECKTIME',
        'CHECKTYPE',
        'VERIFYCODE',
        'SENSORID',
        'Memoinfo',
        'WorkCode',
        'sn',
        'UserExtFmt',
    ];

    protected $casts = [
        'CHECKTIME' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(SqlServerUser::class, 'USERID', 'USERID');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(SqlServerDevice::class, 'sn', 'sn');
    }
}