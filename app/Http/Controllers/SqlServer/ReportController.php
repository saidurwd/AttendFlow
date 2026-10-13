<?php

namespace App\Http\Controllers\SqlServer;

use App\Http\Controllers\Controller;
use App\Models\SqlServerAttendance;
use App\Models\SqlServerDepartment;
use App\Models\SqlServerDevice;
use App\Models\SqlServerUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
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
            ->orderBy('u.NAME');

        if ($request->filled('start_date')) {
            $query->whereDate('c.CHECKTIME', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('c.CHECKTIME', '<=', $request->end_date);
        }
        if ($request->filled('device_serial')) {
            $query->where('c.sn', $request->device_serial);
        }
        if ($request->filled('employee_id')) {
            $query->where('u.BADGENUMBER', $request->employee_id);
        }
        if ($request->filled('department')) {
            $query->where('d.DEPTNAME', $request->department);
        }

        $reports = $query->paginate(50)->withQueryString();

        $employees = SqlServerUser::select('USERID', 'BADGENUMBER', 'NAME', 'DEFAULTDEPTID')
            ->orderBy('NAME')
            ->get();

        $departments = SqlServerDepartment::select('DEPTNAME')
            ->distinct()
            ->orderBy('DEPTNAME')
            ->pluck('DEPTNAME')
            ->filter()
            ->values();

        $devices = SqlServerDevice::select('sn', 'MachineAlias')
            ->where('Enabled', 1)
            ->orderBy('MachineAlias')
            ->get();

        return view('sqlserver.reports.attendance', compact(
            'reports',
            'employees',
            'departments',
            'devices',
            'tz'
        ));
    }
}