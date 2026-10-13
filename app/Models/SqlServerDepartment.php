<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SqlServerDepartment extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'DEPARTMENTS';
    public $timestamps = false;

    protected $primaryKey = 'DEPTID';
    public $incrementing = false;

    protected $fillable = [
        'DEPTID',
        'DEPTNAME',
        'SUPDEPTID',
    ];

    protected $casts = [
        'DEPTID' => 'integer',
        'SUPDEPTID' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(SqlServerUser::class, 'DEFAULTDEPTID', 'DEPTID');
    }
}