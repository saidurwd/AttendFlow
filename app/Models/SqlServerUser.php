<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SqlServerUser extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'USERINFO';
    public $timestamps = false;

    protected $primaryKey = 'USERID';
    public $incrementing = false;

    protected $fillable = [
        'USERID',
        'BADGENUMBER',
        'NAME',
        'GENDER',
        'TITLE',
        'DEFAULTDEPTID',
        'PASSWORD',
        'privilege',
        'CardNo',
    ];

    protected $casts = [
        'USERID' => 'integer',
        'DEFAULTDEPTID' => 'integer',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(SqlServerAttendance::class, 'USERID', 'USERID');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(SqlServerDepartment::class, 'DEFAULTDEPTID', 'DEPTID');
    }
}