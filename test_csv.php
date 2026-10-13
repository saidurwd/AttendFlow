<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SqlServerAttendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$tz = config('app.timezone', 'UTC');

$query = SqlServerAttendance::query()
    ->selectRaw("
        c.USERID,
        u.BADGENUMBER as employee_code,
        u.NAME as name,
        d.DEPTNAME as department,
        m.MachineAlias as device_name,
        m.sn as device_serial,
        CONVERT(date, c.CHECKTIME) as attendance_date,
        MIN(c.CHECKTIME) as clock_in,
        MAX(c.CHECKTIME) as clock_out,
        COUNT(*) as total_entries,
        DATEDIFF(MINUTE, MIN(c.CHECKTIME), MAX(c.CHECKTIME)) as total_minutes
    ")
    ->from('CHECKINOUT as c')
    ->join('USERINFO as u', 'c.USERID', '=', 'u.USERID')
    ->leftJoin('DEPARTMENTS as d', 'u.DEFAULTDEPTID', '=', 'd.DEPTID')
    ->leftJoin('Machines as m', 'c.sn', '=', 'm.sn')
    ->groupBy('c.USERID', 'u.BADGENUMBER', 'u.NAME', 'd.DEPTNAME', 'm.MachineAlias', 'm.sn', DB::raw('CONVERT(date, c.CHECKTIME)'))
    ->orderByDesc('attendance_date')
    ->orderBy('u.NAME')
    ->whereDate('c.CHECKTIME', '>=', '2026-10-01')
    ->whereDate('c.CHECKTIME', '<=', '2026-10-13');

$handle = fopen('php://output', 'w');
fputcsv($handle, ['Date', 'Employee ID', 'Name', 'Department', 'Device', 'Device Serial', 'Clock In', 'Clock Out', 'Total Entries', 'Total Hours']);

$query->chunk(500, function ($rows) use ($handle, $tz) {
    foreach ($rows as $row) {
        $hours = floor($row->total_minutes / 60);
        $mins = $row->total_minutes % 60;
        $totalHours = sprintf('%02d:%02d', $hours, $mins);
        
        fputcsv($handle, [
            Carbon::parse($row->attendance_date)->timezone($tz)->format('Y-m-d'),
            $row->employee_code,
            $row->name,
            $row->department ?? 'N/A',
            $row->device_name ?? 'N/A',
            $row->device_serial,
            Carbon::parse($row->clock_in)->timezone($tz)->format('H:i:s'),
            Carbon::parse($row->clock_out)->timezone($tz)->format('H:i:s'),
            $row->total_entries,
            $totalHours
        ]);
    }
});

fclose($handle);