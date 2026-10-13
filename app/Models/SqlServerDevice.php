<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SqlServerDevice extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Machines';
    public $timestamps = false;

    protected $primaryKey = 'ID';
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'MachineAlias',
        'ConnectType',
        'IP',
        'Port',
        'MachineNumber',
        'Enabled',
        'sn',
    ];

    protected $casts = [
        'ID' => 'integer',
        'Port' => 'integer',
        'MachineNumber' => 'integer',
        'Enabled' => 'boolean',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(SqlServerAttendance::class, 'sn', 'sn');
    }
}